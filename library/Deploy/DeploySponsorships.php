<?php

/**
 * @package     ClawCorpLib
 * @subpackage  com_claw
 *
 * @copyright   (C) 2025 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorpLib\Deploy;

use ClawCorpLib\Enums\PackageInfoTypes;
use ClawCorpLib\Enums\EventSponsorshipTypes;
use Joomla\CMS\Component\ComponentHelper;
use ClawCorpLib\Lib\PackageInfos;

final class DeploySponsorships extends AbstractDeploy
{
  protected function loadPackageInfos(): void
  {
    $filter = [
      PackageInfoTypes::sponsorship,
    ];

    $this->livePackageInfos = new PackageInfos(
      [$this->eventAlias],
      $filter,
      false
    );

    $this->deployedPackageInfos = new PackageInfos(
      [$this->eventAlias],
      $filter,
      false,
      true
    );
  }

  protected function inDeploy(): bool
  {
    $count = 0;


    // Merge general sponsorship categories with the specific community sponsorship
    $sponsorshipCategories = $this->eventInfo->eb_cat_sponsorships;
    $sponsorshipCategories[] = $this->eventInfo->eb_cat_sponsorship[0];

    $componentParams = ComponentHelper::getParams('com_claw');
    $user_email_body = $componentParams->get('sponsorship_registration_email', '');

    // Base times to offset by "time" parameter for each event
    $cancel_before_date = $this->eventInfo->cancelBy;
    $startDate = $this->eventInfo->modify('Wed 9AM');
    $endDate = $this->eventInfo->modify('next Monday midnight');;

    // start and ending usability of these events
    $publish_down = $this->eventInfo->modify('+8 days');

    /** @var \ClawCorpLib\Lib\PackageInfo */
    foreach ($this->livePackageInfos->packageInfoArray as $packageInfo) {
      $this->aliases[$packageInfo->id] = $this->formatAlias([$packageInfo->title]);
    }

    if (!$this->ValidateAliases()) {
      return false;
    }

    /** @var \ClawCorpLib\Lib\PackageInfo */
    foreach ($this->livePackageInfos->packageInfoArray as $packageInfo) {
      $packageInfo->alias = $this->aliases[$packageInfo->id];
      $end = clone ($endDate);
      $cutoff = clone ($startDate);

      if (count($packageInfo->meta) != 1) {
        $this->Log("Skipping $packageInfo->title due to invalid sponsorship classification", 'text-warning');
        continue;
      }

      if (!in_array($packageInfo->category, $sponsorshipCategories)) {
        $this->Log(print_r($packageInfo, 'text-danger'));
        $this->Log("Sponsorship category not present in event $packageInfo->eventAlias", 'text-danger');
        return false;
      }

      switch ((int)$packageInfo->meta[0]) {
        // We need advertising submitted no later than 3 weeks before the event
        case (EventSponsorshipTypes::advertising->value):
          $cutoff->modify('-3 weeks');
          break;

        case (EventSponsorshipTypes::logo->value):
          $cutoff->modify('-1 week');
          break;

        // Buffer until next event
        case (EventSponsorshipTypes::master_sustaining->value):
          $cutoff->modify('+6 months');
          $end = $cutoff;
          $publish_down = $cutoff;
          break;

        // Blue, black, gold are all the same
        case (EventSponsorshipTypes::black->value):
        case (EventSponsorshipTypes::blue->value):
        case (EventSponsorshipTypes::gold->value):
          $cutoff->modify('-1 week');
          break;

        // Leather heart donations are available until the end of the event
        case (EventSponsorshipTypes::community->value):
          $cutoff = clone ($endDate);
          break;

        default:
          var_dump($packageInfo);
          die('Invalid sponsorship classification');
          break;
      }

      $response = $this->SyncEvent(
        new EbSyncItem(
          eventInfo: $this->eventInfo,
          id: $packageInfo->eventId,
          published: $packageInfo->published->value,
          main_category_id: $packageInfo->category,
          alias: $this->aliases[$packageInfo->id],
          title: $this->eventInfo->prefix . ' ' . $packageInfo->title,
          description: $packageInfo->description ? $packageInfo->description : $packageInfo->title,
          article_id: $this->eventInfo->termsArticleId,
          cancel_before_date: $cancel_before_date,
          cut_off_date: $cutoff,
          event_date: $startDate,
          event_end_date: $end,
          publish_down: $publish_down,
          individual_price: $packageInfo->fee,
          registration_start_date: $this->registration_start_date,
          registration_access: $this->registered_acl,
          user_email_body: $user_email_body,
          payment_methods: '2,5' // Credit Card, Invoice
        )
      );

      $count += $this->HandleResponseStandard($response, $packageInfo);
    }

    $this->Log("Deployed $count sponsorships.");

    return true;
  }

  protected function formatAlias(array $in): string
  {
    return strtolower($this->eventInfo->prefix . '_spo_' . preg_replace("/[^A-Za-z0-9]+/", '_', $in[0]));
  }
}
