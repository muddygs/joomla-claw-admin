<?php

namespace Clawcorp\Plugin\Content\Clawreg\Extension;

// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

use ClawCorpLib\Helpers\EventBooking;
use ClawCorpLib\Enums\EventPackageTypes;
use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Lib\EventConfig;
use ClawCorpLib\Lib\PackageInfo;

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
      // 3: display option (null, onsite_active) 
      $matcheslist = explode(',', $match[1]);

      $output = $this->paramsToButton($matcheslist);

      // We should replace only first occurrence in order to allow positions with the same name to regenerate their content:
      if (($start = strpos($article->text, $match[0])) !== false) {
        $article->text = substr_replace($article->text, $output, $start, \strlen($match[0]));
      }
    }
  }

  public function errorButton(): string
  {
    return "[ error ]";
  }

  private function paramsToButton($params)
  {
    if (count($params) < 3) return $this->errorButton();
    $params = array_map('trim', $params);
    #$params = array_map('strtolower', $params);

    // 0 - Meta-Location
    // TODO: update when database table is ready

    $metaLocation = $this->parseMetaLocation($params[0]);
    $packageType = $this->parsePackageType($params[1]);
    $buttonText = $params[2];
    $displayOption = sizeof($params) > 3 ? $this->parseDisplayOption($params[3]) : '';

    // 2 - either missing or "any" only
    if (0 == $metaLocation || null === $packageType || null === $displayOption) {
      //dd([$metaLocation, $packageType, $displayOption]);
      return $this->errorButton();
    }

    // Seems valid, let's try to find an active event for it
    $eventAlias = Aliases::currentByLocation($metaLocation);
    $eventConfig = new EventConfig(alias: $eventAlias, publishedOnly: true);
    $packageInfo = $eventConfig->getPackageInfo($packageType);

    if (is_null($packageInfo)) return $this->errorButton();

    $buttonText = $this->parseButtonText($packageInfo, $buttonText);

    $link = EventBooking::buildRegistrationLink(
      $eventAlias,
      $packageType
    );

    return '<a href="' . $link . '" role="button" class="btn btn-danger">' . $buttonText . '</a>';
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
  private function parsePackageType(string $param): ?EventPackageTypes
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

  private function parseDisplayOption(string $param): string
  {
    return match ($param) {
      'any' => 'any',
      default => null,
    };
  }
}
