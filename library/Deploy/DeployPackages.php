<?php

/**
 * @package     ClawCorpLib
 * @subpackage  com_claw
 *
 * @copyright   (C) 2025 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorpLib\Deploy;

use ClawCorpLib\Enums\EventPackageTypes;
use ClawCorpLib\Enums\PackageInfoTypes;
use ClawCorpLib\Enums\EbPublishedState;
use ClawCorpLib\Lib\EventConfig;
use ClawCorpLib\Lib\EventInfo;
use ClawCorpLib\Lib\PackageInfo;
use ClawCorpLib\Helpers\EventBooking;
use ClawCorpLib\Helpers\Redirects;
use ClawCorpLib\Lib\PackageInfos;
use Joomla\CMS\Factory;

final class DeployPackages extends AbstractDeploy
{
  protected function loadPackageInfos(): void
  {
    $this->livePackageInfos = new PackageInfos(
      [$this->eventAlias],
      EventConfig::DEFAULT_FILTERS,
      false
    );

    $this->deployedPackageInfos = new PackageInfos(
      [$this->eventAlias],
      EventConfig::DEFAULT_FILTERS,
      false,
      true
    );
  }

  protected function inDeploy(): bool
  {
    if (!count($this->livePackageInfos->packageInfoArray)) {
      $this->Log("No changes to deploy");
      return true;
    }

    $count = 0;

    $metaPackages = [
      EventPackageTypes::vip,
      EventPackageTypes::claw_staff,
      EventPackageTypes::claw_board,
    ];

    // Base times to offset by "time" parameter for each event
    $cancel_before_date = $this->eventInfo->cancelBy;
    $startDateWed = $this->eventInfo->modify('Wed 9AM');
    $endDate = $this->eventInfo->modify('next Monday midnight');;

    $publish_down = $this->eventInfo->modify('+8 days');

    // Check for potential duplicate aliases
    /** @var \ClawCorpLib\Lib\PackageInfo */
    foreach ($this->livePackageInfos->packageInfoArray as $packageInfo) {
      $tname = str_replace('_', '-', $packageInfo->eventPackageType->name);

      switch ($packageInfo->packageInfoType) {
        case PackageInfoTypes::passes:
        case PackageInfoTypes::passes_other:
          $tname = preg_replace('/[^\S]+/', '-', $packageInfo->title);
          break;
      }

      $this->aliases[$packageInfo->id] = $this->formatAlias([$tname]);
    }

    if (!$this->ValidateAliases()) {
      return false;
    }

    /** @var \ClawCorpLib\Lib\PackageInfo */
    foreach ($this->livePackageInfos->packageInfoArray as $packageInfo) {
      // See: administrator/components/com_claw/forms/packageinfo.xml
      //showon="packageInfoType:3[OR]eventPackageType:3[OR]eventPackageType:32[OR]eventPackageType:20"
      if (in_array($packageInfo->eventPackageType, $metaPackages) && count($packageInfo->meta) == 0) {
        $this->Log("Meta Missing: $packageInfo->title", 'text-warning');
        continue;
      }

      $start = $packageInfo->start;
      $end = $packageInfo->end;
      $cutoff = $endDate;
      $packageInfo->alias = $this->aliases[$packageInfo->id];

      $accessGroup = $packageInfo->acl_id > 0 ? $packageInfo->acl_id : $this->registered_acl;
      $reg_start_date = $this->registration_start_date;

      $price_text = '';
      $enable_cancel_registration = 1;

      switch ($packageInfo->packageInfoType) {
        case PackageInfoTypes::combomeal:
          // Update internal start/end information for main packages to guarantee
          // Wed-Sun on the package span; all others used packageInfo directly (above)
          $packageInfo->start = $startDateWed;
          $packageInfo->end = $endDate;
          $start = $startDateWed;
          $end = $endDate;

          if ($packageInfo->bundleDiscount > 0) {
            $price_text = '$' . $packageInfo->fee . ' (attendee) / $' . $packageInfo->fee - $packageInfo->bundleDiscount . ' (volunteer)';
          }
          break;

        case PackageInfoTypes::main:
          // Update internal start/end information for main packages to guarantee
          // Wed-Sun on the package span; all others used packageInfo directly (above)
          $packageInfo->start = $startDateWed;
          $packageInfo->end = $endDate;
          $start = $startDateWed;
          $end = $endDate;

          // If this event is supposed to have meta data, but it is not setup, skip for now

          if ($packageInfo->bundleDiscount > 0) {
            $price_text = '$' . $packageInfo->fee . ' (attendee) / $' . $packageInfo->fee - $packageInfo->bundleDiscount . ' (volunteer)';
          }
          break;

        case PackageInfoTypes::vendormart:
          // VendorMart runs Fri-Sun
          $start = $packageInfo->start = $this->eventInfo->modify('Fri 9AM');
          $end = $packageInfo->end = $this->eventInfo->modify('Sun 6PM');;
          $price_text = 'Price depends on options';
          break;

        case PackageInfoTypes::addon:
          $interval = $packageInfo->start->diff($packageInfo->end);

          // If the event is less than 8 hours, then the cutoff is 3 hours before the event
          if ($interval->h <= 8) {
            $cutoff = Factory::getDate($packageInfo->start);
            $cutoff = $cutoff->modify('-3 hours');
          }

          if ($packageInfo->bundleDiscount > 0) {
            $price_text = '$' . $packageInfo->fee . ' (attendee) / $' . $packageInfo->fee - $packageInfo->bundleDiscount . ' (volunteer)';
          }
          break;

        case PackageInfoTypes::daypass:
          $reg_start_date = $startDateWed;
          break;

        case PackageInfoTypes::passes:
        case PackageInfoTypes::passes_other:
          $cutoff = null;
          $cancel_before_date = null;
          // Remove any non-ascii char from title
          $accessGroup = $this->public_acl;
          $reg_start_date = $packageInfo->packageInfoType == PackageInfoTypes::passes ? $startDateWed : $this->registration_start_date;
          $enable_cancel_registration = 0;
          break;

        default:
          continue 2;
          break;
      }

      $response = $this->SyncEvent(
        new EbSyncItem(
          eventInfo: $this->eventInfo,
          published: $packageInfo->published->value,
          id: $packageInfo->eventId,
          main_category_id: $packageInfo->category,
          alias: $this->aliases[$packageInfo->id],
          title: $this->eventInfo->prefix . ' ' . $packageInfo->title,
          description: $packageInfo->description ? $packageInfo->description : $packageInfo->title,
          article_id: $this->eventInfo->termsArticleId,
          cancel_before_date: $cancel_before_date,
          cut_off_date: $cutoff,
          event_date: $start,
          event_end_date: $end,
          publish_down: $publish_down,
          individual_price: $packageInfo->fee,
          price_text: $price_text,
          registration_start_date: $reg_start_date,
          registration_access: $accessGroup,
          enable_cancel_registration: $enable_cancel_registration
        )
      );

      $count += $this->HandleResponseStandard($response, $packageInfo);

      $this->createRedirect($this->eventInfo, $packageInfo);
    }

    // Special friendly redirects cases
    // addons
    $suffix = EventPackageTypes::addons->toLink();
    $fromLink = strtolower($this->eventInfo->prefix . '-reg-' . $suffix);
    $toLink = EventBooking::buildRegistrationLink($this->eventAlias, EventPackageTypes::addons);
    $redirect = new Redirects($this->db, '/' . $fromLink, $toLink, $fromLink);
    $redirect->insert();

    $this->Log("Deployed $count packages.");

    // TODO: sync price changes
    $this->VolunteerDiscountBundles();
    $this->MetaDiscountBundles();

    return true;
  }

  protected function formatAlias(array $in): string
  {
    return strtolower($this->eventInfo->prefix . '-' . $in[0]);
  }

  private function createRedirect(EventInfo $eventInfo, PackageInfo $packageInfo)
  {
    $suffix = $packageInfo->eventPackageType->toLink();
    if ($suffix != '') {
      $fromLink = strtolower($eventInfo->prefix . '-reg-' . $suffix);
      $toLink = EventBooking::buildRegistrationLink($this->eventAlias, $packageInfo->eventPackageType);
      $redirect = new Redirects($this->db, '/' . $fromLink, $toLink, $fromLink);
      $redirect->insert();
    }
  }

  public function VolunteerDiscountBundles(): string
  {
    $count = 0;

    $mainEventConfig = new EventConfig($this->eventAlias, [PackageInfoTypes::main]);
    $prefix = $mainEventConfig->eventInfo->prefix;

    $mainPackages = $mainEventConfig->packageInfos;

    $addonsEventConfig = new EventConfig($this->eventAlias, [PackageInfoTypes::addon, PackageInfoTypes::combomeal]);
    $addonsPackages = $addonsEventConfig->packageInfos;

    /** @var \ClawCorpLib\Lib\PackageInfo */
    foreach ($mainPackages as $packageInfo) {
      if ($packageInfo->published != EbPublishedState::published) {
        continue;
      }

      if (!$packageInfo->isVolunteer) {
        continue;
      }

      if ($packageInfo->eventId == 0) {
        $this->Log("Skipping Discount Bundle (Event ID 0): $packageInfo->title");
        continue;
      }

      /** @var \ClawCorpLib\Lib\PackageInfo */
      foreach ($addonsPackages as $addon) {
        if ($addon->published != EbPublishedState::published) {
          continue;
        }

        if ($addon->eventId == 0) {
          $this->Log("Skipping Discount Bundle (Event ID 0): $addon->title");
          continue;
        }

        if ($addon->bundleDiscount == 0) {
          $this->Log("Skipping Discount Bundle (No Discount): $addon->title");
          continue;
        }

        if ($this->addDiscountBundle($addon->bundleDiscount, $prefix, $packageInfo, $addon)) {
          $count++;
        }
      }
    }

    $this->Log("Updated $count discount bundles.");
    return $this->FormatLog();
  }

  /**
   * Helper function for creating discount bundles among events by $ amount
   * @param array Array of PackageInfo objects
   * @param int Dollar amount
   * @return bool  True if successful
   */
  private function addDiscountBundle(int $dollarAmount, string $prefix, \ClawCorpLib\Lib\PackageInfo ...$packageInfos): bool
  {
    if (count($packageInfos) < 2) {
      $this->Log("Skipping discount bundle: Not enough events");
      return false;
    }

    $eventIds = [];
    $titles = [];

    foreach ($packageInfos as $packageInfo) {
      if ($packageInfo->eventId == 0) {
        $this->Log("Skipping discount bundle: Invalid event ID");
        return false;
      }
      $eventIds[] = $packageInfo->eventId;
      $titles[] = $packageInfo->title;
    }

    $db = $this->db;

    // Check for existing discount
    $query = $db->getQuery(true);
    $query->select('discount_id')
      ->from('#__eb_discount_events')
      ->where('event_id IN (' . implode(',', $eventIds) . ')')
      ->group('discount_id')
      ->having('COUNT(DISTINCT event_id) = ' . count($eventIds));
    $db->setQuery($query);
    $result = $db->loadResult();

    if ($result != null) {
      $this->Log("Skipping duplicate discount: $result");
      return false;
    }

    array_unshift($titles, $prefix);
    $title = implode('-', $titles);

    $query = $db->getQuery(true);

    $data = (object)[
      'id' => 0,
      'title' => $title,
      'event_ids' => implode(',', $eventIds),
      'discount_amount' => $dollarAmount,
      'from_date' => $query->nullDate(false),
      'to_date' => $query->nullDate(false),
      'times' => 0,
      'used' => 0,
      'published' => EbPublishedState::published->value,
      'number_events' => 0,
      'discount_type' => 1
    ];

    $result = $db->insertObject('#__eb_discounts', $data, 'id');

    if ($result === false) {
      $this->Log("Error adding discount: $title", 'text-danger');
      return false;
    }

    foreach ($eventIds as $eventId) {
      $discount = (object)[
        'id' => 0,
        'discount_id' => $data->id,
        'event_id' => $eventId
      ];

      $result = $db->insertObject('#__eb_discount_events', $discount, 'id');

      if ($result === false) {
        $this->Log("Error adding discount events: $title", 'text-danger');
        return false;
      }
    }

    $this->Log("Added discount: $title (\$$dollarAmount)");
    return true;
  }

  public function MetaDiscountBundles()
  {
    $count = 0;

    $eventConfig = new EventConfig($this->eventAlias, []);
    $prefix = $eventConfig->eventInfo->prefix;
    $mainPackages = $eventConfig->packageInfos;

    /** @var \ClawCorpLib\Lib\PackageInfo */
    foreach ($mainPackages as $packageInfo) {
      if (
        !is_array($packageInfo->meta)
        || count($packageInfo->meta) == 0
        || $packageInfo->isVolunteer
        || $packageInfo->packageInfoType != PackageInfoTypes::main
        || $packageInfo->published != EbPublishedState::published
      ) {
        continue;
      }

      /** @var \ClawCorpLib\Lib\PackageInfo */
      foreach ($packageInfo->meta as $eventId) {
        // Retrieve the addon PackageInfo since $addon is an Event Booking event id
        $addon = null;

        /** @var \ClawCorpLib\Lib\PackageInfo */
        foreach ($mainPackages as $subPackageInfo) {
          if ($subPackageInfo->eventId == $eventId) {
            $addon = $subPackageInfo;
            break;
          }
        }

        if (is_null($addon)) continue;

        if ($this->addDiscountBundle($addon->fee, $prefix, $packageInfo, $addon)) {
          $count++;
        }
      }
    }

    $this->Log("Updated $count meta discount bundles.");
  }
}
