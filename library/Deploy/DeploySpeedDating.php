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

class DeploySpeedDating extends AbstractDeploy
{
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
          var_dump($this->aliases);
          dd([$packageInfo, $metaKey, $metaRow]);
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
