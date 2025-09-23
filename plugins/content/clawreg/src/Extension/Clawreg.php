<?php

namespace Clawcorp\Plugin\Content\Clawreg\Extension;

// no direct access
defined('_JEXEC') or die;

use ClawCorpLib\Enums\EbPublishedState;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

use ClawCorpLib\Helpers\EventBooking;
use ClawCorpLib\Enums\EventPackageTypes;
use ClawCorpLib\Iterators\PackageInfoArray;
use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Lib\EventConfig;
use ClawCorpLib\Lib\PackageInfo;
use Joomla\CMS\Date\Date;

class Clawreg extends CMSPlugin implements SubscriberInterface
{
  public static function getSubscribedEvents(): array
  {
    return [
      'onContentPrepare' => 'createButton',
    ];
  }

  public function createButton(Event $event)
  {
    if (!$this->getApplication()->isClient('site')) {
      return;
    }

    [$context, $article, $params, $page] = array_values($event->getArguments());
    #if ($context !== "com_content.article") return;

    // no plugin marker in article, we're done
    if (!str_contains($article->text, "{clawreg")) return;

    // $matches[0] is full pattern match, $matches[1] is the position
    $regex         = "/{clawreg\s+([\w\s,\"\#\(\)]+)}/i";
    preg_match_all($regex, $article->text, $matches, PREG_SET_ORDER);

    // No matches, we're done
    if (!$matches) return;

    foreach ($matches as $match) {
      // ordering:
      // 0: location
      // 1: EventPackageType enum case | packageId
      // 2: displayed text with limited substitutions available
      // 3: display option (null, any, onsite_active) 
      $matcheslist = explode(',', $match[1]);

      $output = $this->paramsToButton($matcheslist);

      // We should replace only first occurrence in order to allow positions with the same name to regenerate their content:
      if (($start = strpos($article->text, $match[0])) !== false) {
        $article->text = substr_replace($article->text, $output, $start, \strlen($match[0]));
      }
    }
  }

  private function errorButton(string $msg = ''): string
  {
    return "[ error $msg ]";
  }

  private function expiredButton(): string
  {
    return '<a href="javascript:void(0)" role="button" class="btn btn-lg btn-info">Registration Closed</a>';
  }

  private function paramsToButton($params): string
  {
    if (count($params) < 3) return $this->errorButton('- Missing parameters');
    $params = array_map('trim', $params);

    // 0 - Meta-Location
    // TODO: update when database table is ready

    $metaLocation = $this->parseMetaLocation($params[0]);
    if (0 == $metaLocation) {
      return $this->errorButton('- Bad location');
    }

    $packageType = $this->parsePackageType($params[1]);
    $buttonText = $params[2];
    $displayOption = sizeof($params) > 3 ? $this->parseDisplayOption($params[3]) : '';

    // 2 - either missing or "any" only
    if (null === $packageType || null === $displayOption) {
      return $this->errorButton('- bad display option');
    }

    // Seems valid, let's try to find an active event for it
    $eventAlias = Aliases::currentByLocation($metaLocation);
    $eventConfig = new EventConfig(alias: $eventAlias, filter: [], publishedOnly: true);

    if (gettype($packageType) == 'object') {
      if (in_array($packageType, [
        EventPackageTypes::pass,
        EventPackageTypes::day_pass_fri,
        EventPackageTypes::day_pass_sat,
        EventPackageTypes::day_pass_sun,
        EventPackageTypes::pass_other
      ])) {
        return $this->passes($eventConfig, $packageType, $displayOption, $buttonText);
      }

      $packageInfo = $eventConfig->getPackageInfo($packageType);

      if (is_null($packageInfo)) {
        return $this->errorButton('- unknown package type');
      }

      if ($packageInfo->published != EbPublishedState::published || !$packageInfo->eventId) {
        return $this->errorButton("- not published ($packageInfo->title:$packageInfo->eventAlias:$packageInfo->eventId)");
      }

      $buttonText = $this->parseButtonText($packageInfo, $buttonText);

      if ($packageType == EventPackageTypes::vendormart) {
        $link = EventBooking::buildDirectLink($packageInfo->eventId);
      } else {
        $link = EventBooking::buildRegistrationLink($eventAlias, $packageType);
      }

      $link = '<a href="' . $link . '" role="button" class="btn btn-lg btn-large btn-danger">' . $buttonText . '</a>';
      $row = EventBooking::loadEventRow($packageInfo->eventId);
      $endDate = new Date($row->event_end_date);
    }

    // Direct link to specific event id
    if (gettype($packageType) == 'int') {
      $row = EventBooking::loadEventRow($packageType);

      if (is_null($row) || $row->published != EbPublishedState::published->value) {
        return $this->errorButton('- null/unpublished package');
      }

      $endDate = new Date($row->event_end_date);
    }

    $now = new Date();

    return $now > $endDate ? $this->expiredButton() : $link;
  }

  private function passes(EventConfig $eventConfig, EventPackageTypes $packageType, string $displayOption, string $buttonText): string
  {
    $show = false;

    switch ($packageType) {
      case EventPackageTypes::pass:
        if ($eventConfig->eventInfo->passesActive) $show = true;
        break;

      case EventPackageTypes::pass_other:
        if ($eventConfig->eventInfo->passesOtherActive) $show = true;
        break;
      case EventPackageTypes::day_pass_fri:
      case EventPackageTypes::day_pass_sat:
      case EventPackageTypes::day_pass_sun:
        if ($eventConfig->eventInfo->dayPassesActive) $show = true;
        break;
      default:
        break;
    }

    if ($show) {
      $packageInfoArray = $eventConfig->getPackageInfos($packageType);
      $html = $this->processButtonGroup($packageInfoArray, $buttonText, $displayOption);
      return $html;
    } else {
      return '';
    }
  }

  private function processButtonGroup(PackageInfoArray $packageInfoArray, string $buttonText, ?string $displayOption): string
  {
    ob_start();
    $dayStart = new Date('today midnight');
    $dayEnd = new Date('tomorrow 2am');
    $now = new Date();

?>
    <div class="d-grid gap-2">
      <?php

      /** @var \ClawCorpLib\Lib\PackageInfo */
      foreach ($packageInfoArray as $packageInfo) {
        if ($packageInfo->published != EbPublishedState::published || !$packageInfo->eventId) {
          continue;
        }

        $row = EventBooking::loadEventRow($packageInfo->eventId);
        $rowStart = new Date($row->event_date);
        $rowEnd = new Date($row->event_end_date);

        if (is_null($row)) {
          echo $this->errorButton("- invalid event id $packageInfo->eventId");
          continue;
        }

        if ((is_null($displayOption) && $rowStart >= $dayStart && $rowEnd <= $dayEnd) || ($displayOption == 'any' && $now <= $rowEnd)) {
          $link = EventBooking::buildDirectLink($packageInfo->eventId);
          $title = $this->parseButtonText($packageInfo, $buttonText);
          echo '<a href="' . $link . '" role="button" class="btn btn-lg btn-danger">' . $title . '</a>';
        }
      }
      ?>
    </div>
<?php

    return ob_get_clean();
  }

  private function parseMetaLocation(string $param): int
  {
    $metaLocation = match (strtolower($param)) {
      'cleveland' => 1,
      'losangeles' => 2,
      default => 0
    };

    return $metaLocation;
  }

  // a fully-numeric packageType means we're going to lookup the package by database id
  // otherwise, we treat as EventPackageType 
  private function parsePackageType(string $param): EventPackageTypes|int|null
  {
    if (preg_match('/^[1-9]\d*$/', $param)) {
      $packageType = EventPackageTypes::tryFrom((int)$param);
    } else {
      $packageType = EventPackageTypes::fromName(strtolower($param));
    }

    return $packageType;
  }

  private function parseButtonText(PackageInfo $packageInfo, string $param): string
  {
    $day = $packageInfo->start == null ? '' : $packageInfo->start->format('D');
    // Possible substitutions
    $patterns = [
      '#title#' => $packageInfo->title,
      '#fee#' => '$' . round($packageInfo->fee),
      '#day#' => $day,
    ];

    $param = str_replace(array_keys($patterns), array_values($patterns), $param);

    return $param;
  }

  private function parseDisplayOption(string $param): ?string
  {
    return match ($param) {
      'any' => 'any',
      'onsite' => 'onsite',
      default => null,
    };
  }
}
