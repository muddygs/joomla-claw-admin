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
use ClawCorpLib\Lib\PackageInfos;

final class DeployEquipmentRental extends AbstractDeploy
{
  protected function loadPackageInfos(): void
  {
    $filter = [
      PackageInfoTypes::equipment,
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

    // Base times to offset by "time" parameter for each event
    $cancel_before_date = $this->eventInfo->cancelBy;
    $startDateWed = $this->eventInfo->modify('Wed 9AM');
    $publish_down = $this->eventInfo->modify('+8 days');

    // check for duplicates
    /** @var \ClawCorpLib\Lib\PackageInfo */
    foreach ($this->livePackageInfos->packageInfoArray as $packageInfo) {
      $name = preg_replace('/[^\S]+/', '-', $packageInfo->title);
      $this->aliases[$packageInfo->id] = $this->formatAlias([$name]);
    }

    if (!$this->ValidateAliases()) {
      return false;
    }

    /** @var \ClawCorpLib\Lib\PackageInfo */
    foreach ($this->livePackageInfos->packageInfoArray as $packageInfo) {
      $packageInfo->alias = $this->aliases[$packageInfo->id];
      $accessGroup = $packageInfo->acl_id > 0 ? $packageInfo->acl_id : $this->registered_acl;

      $price_text = '';

      $deposit_fee = count($packageInfo->meta) > 0 ? $packageInfo->meta[0] : 0;
      if ($deposit_fee > 0) {
        $price_text = '$' . $packageInfo->fee . ' (rental) + $' . $deposit_fee . ' (refundable deposit)';
        $packageInfo->fee += $deposit_fee;
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
          cut_off_date: $startDateWed,
          event_date: $packageInfo->start,
          event_end_date: $packageInfo->end,
          publish_down: $publish_down,
          individual_price: $packageInfo->fee,
          price_text: $price_text,
          registration_start_date: $this->registration_start_date,
          registration_access: $accessGroup,
          location_id: $this->eventInfo->ebLocationId,
        )
      );

      $count += $this->HandleResponseStandard($response, $packageInfo);
    }

    $this->Log("Deployed $count packages.");

    return true;
  }

  protected function formatAlias(array $in): string
  {
    return strtolower($this->eventInfo->prefix . '-' . $in[0]);
  }
}
