<?php

/**
 * @package     ClawCorpLib
 * @subpackage  com_claw
 *
 * @copyright   (C) 2025 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorpLib\Deploy;

use ClawCorpLib\EbInterface\EbEventTable;
use ClawCorpLib\Enums\EbPublishedState;
use ClawCorpLib\Enums\PackageInfoTypes;
use ClawCorpLib\Lib\PackageInfos;

class DeploySpeedDating extends AbstractDeploy
{
  private array $metaUnpublishEventIds = [];

  protected function loadPackageInfos(): void
  {
    $filter = [
      PackageInfoTypes::speeddating,
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

  protected function beforeDeploy(): bool
  {
    if (!parent::beforeDeploy()) return false;

    // For all the livePackageInfo changes, need to find delete meta events
    // meta is an array of objects
    /** @var \ClawCorpLib\Lib\PackageInfo $packageInfo */
    foreach ($this->livePackageInfos->packageInfoArray as $packageInfo) {
      if (isset($this->deployedPackageInfos->packageInfoArray[$packageInfo->id])) {
        $deployedEventIds = array_column((array)$this->deployedPackageInfos->packageInfoArray[$packageInfo->id]->meta, 'eventId');
        $liveEventIds = array_column((array)$packageInfo->meta, 'eventId');
      } else {
        continue;
      }

      $deletedEventIds = array_diff($deployedEventIds, $liveEventIds);
      $deletedEventIds = array_filter($deletedEventIds, fn($v) => (int)$v != 0);

      if (count($deletedEventIds))
        $this->metaUnpublishEventIds = array_merge($this->metaUnpublishEventIds, $deletedEventIds);
    }

    // Now, process fully-deleted packages
    /** @var \ClawCorpLib\Lib\PackageInfo $packageInfo */
    foreach ($this->deletedPackageInfoArray as $packageInfo) {
      $deletedEventIds = array_column($packageInfo->meta, 'eventId');
      if (count($deletedEventIds))
        $this->metaUnpublishEventIds = array_merge($this->metaUnpublishEventIds, $deletedEventIds);
    }

    return true;
  }

  protected function afterDeploy(): bool
  {
    if (!parent::afterDeploy()) return false;

    foreach ($this->metaUnpublishEventIds as $eventId) {
      $eventTable = EbEventTable::load($eventId);
      $eventTable->published = EbPublishedState::any->value;
      $eventTable->update();
      $this->Log("Unpublished EventBooking ID: $eventId");
    }

    return true;
  }

  protected function inDeploy(): bool
  {
    $count = 0;

    /** @var \ClawCorpLib\Lib\PackageInfo */
    foreach ($this->livePackageInfos->packageInfoArray as $packageInfo) {
      foreach ($packageInfo->meta as $metaKey => $metaRow) {
        $role = $metaRow->role;
        $this->aliases[$packageInfo->id . $role] = $this->formatAlias([$packageInfo->title, $role]);
      }
    }

    if (!$this->ValidateAliases()) {
      return false;
    }

    /** @var \ClawCorpLib\Lib\PackageInfo */
    foreach ($this->livePackageInfos->packageInfoArray as $packageInfo) {
      foreach ($packageInfo->meta as $metaKey => $metaRow) {
        $role = $metaRow->role;
        $event_capacity = $metaRow->limit;
        $eventId = $metaRow->eventId;

        if (is_null($this->aliases[$packageInfo->id . $role])) {
          $this->Log("Unexpected alias in speeddating (package id $packageInfo->id):", "text-danger");
          $this->Log(print_r($this->aliases, true));
          return false;
        }

        $packageInfo->alias = $this->aliases[$packageInfo->id . $role];

        $start = $packageInfo->start;
        $end = $packageInfo->end;
        $cancel_before_date = $start;
        $cutoff = $start;

        $title = $this->eventInfo->prefix . ' ' . $packageInfo->title . ' (' . $role . ')';

        $response = $this->SyncEvent(
          new EbSyncItem(
            eventInfo: $this->eventInfo,
            id: $eventId,
            published: $packageInfo->published->value,
            main_category_id: $packageInfo->category,
            alias: $this->aliases[$packageInfo->id . $role],
            title: $title,
            description: $packageInfo->description ? $packageInfo->description : $packageInfo->title,
            article_id: $this->eventInfo->termsArticleId,
            cancel_before_date: $cancel_before_date,
            cut_off_date: $cutoff,
            event_date: $start,
            event_end_date: $end,
            publish_down: $end,
            individual_price: 0,
            registration_start_date: $this->registration_start_date,
            registration_access: $this->registered_acl,
            event_capacity: $event_capacity,
          )
        );

        if ($packageInfo->id == 405) {
          var_dump([$response, $metaKey]);
        }

        $count += $this->HandleResponseMeta($response, $packageInfo, $metaKey);
      }
    }

    $this->Log("Deployed $count speed dating packages.");

    return true;
  }

  protected function formatAlias(array $in): string
  {
    return strtolower(preg_replace('/[^\S]+/', '_', implode('-', [$this->eventInfo->prefix, 'sd', ...$in])));
  }
}
