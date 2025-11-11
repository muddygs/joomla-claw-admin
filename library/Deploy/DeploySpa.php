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
use ClawCorpLib\Helpers\Helpers;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\User\UserFactoryInterface;
use ClawCorpLib\Lib\PackageInfos;
use ClawCorpLib\EbInterface\EbEventTable;
use ClawCorpLib\Enums\EbPublishedState;

final class DeploySpa extends AbstractDeploy
{
  private array $metaUnpublishEventIds = [];

  protected function loadPackageInfos(): void
  {
    $filter = [
      PackageInfoTypes::spa,
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

    $userEmails = [];
    // If a public name is not set for a therapist, we will abort until the admin
    // fixes
    $publicNames = [];

    $params = ComponentHelper::getParams('com_claw');
    $publicNameFieldId = $params->get('public_name_field', 0);

    if (0 == $publicNameFieldId) {
      $this->Log('Public name field not set in global configuration', 'text-danger');
      return false;
    }

    // Load default email for EB from DB to merge with therapist email
    $query = $this->db->getQuery(true);
    $query->select('config_value')
      ->from('#__eb_configs')
      ->where($this->db->qn('config_key') . '=' . $this->db->q('notification_emails'));
    $this->db->setQuery($query);
    $defaultEmail = $this->db->loadResult();

    // end time can deal with the cutoff individually
    $cutoff = $this->eventInfo->modify('next Monday midnight');

    // Check for duplicates and collect id information for therapists
    /** @var \ClawCorpLib\Lib\PackageInfo */
    foreach ($this->livePackageInfos->packageInfoArray as $packageInfo) {
      // The $userid in meta points to a therapist user id - one event/therapist/time
      $index = 0;

      foreach ($packageInfo->meta as $metaKey => $metaRow) {
        $eventId = $metaRow->eventId;
        $userId = $metaRow->userid;

        if (!array_key_exists($metaRow->userid, $userEmails)) {
          /** @var \Joomla\CMS\User\UserFactoryInterface */
          $userFactory = Factory::getContainer()->get(UserFactoryInterface::class);
          $user = $userFactory->loadUserById($metaRow->userid);
          if (is_null($user)) {
            $this->Log('Spa therapist user id invalid: ' . $metaRow->userid, 'text-danger');
            return false;
          }

          $publicName = Helpers::getUserField($metaRow->userid, $publicNameFieldId);
          if (is_null($publicName)) {
            $this->Log('User missing public name in user configuration: ' . $metaRow->userid, 'text-danger');
            return false;
          }

          $userEmails[$userId] = $user->email;
          $publicNames[$userId] = $publicName;
        }

        $this->aliases[$packageInfo->id . $userId . $index] = $this->formatAlias([$packageInfo->title, $publicNames[$userId], $index++]);
      }
    }

    if (!$this->ValidateAliases()) {
      return false;
    }

    /** @var \ClawCorpLib\Lib\PackageInfo */
    foreach ($this->livePackageInfos->packageInfoArray as $packageInfo) {
      // The $userid in meta points to a therapist user id - one event/therapist/time
      $index = 0;

      foreach ($packageInfo->meta as $metaKey => $metaRow) {
        $start = $packageInfo->start;
        $end = $packageInfo->end;
        $cancel_before_date = $start;
        $userId = $metaRow->userid;

        $title = $this->eventInfo->prefix . ' ' . $packageInfo->title . '(' . $publicName[$userId] . ')';
        $packageInfo->alias = $this->aliases[$packageInfo->id . $userId . $index];

        $response = $this->SyncEvent(
          new EbSyncItem(
            eventInfo: $this->eventInfo,
            id: $eventId,
            published: $packageInfo->published->value,
            article_id: $this->eventInfo->termsArticleId,
            cancel_before_date: $cancel_before_date,
            cut_off_date: $cutoff,
            description: $packageInfo->description ? $packageInfo->description : $packageInfo->title,
            event_capacity: 1,
            event_date: $start,
            event_end_date: $end,
            individual_price: $packageInfo->fee,
            alias: $this->aliases[$packageInfo->id . $userId . $index],
            main_category_id: $packageInfo->category,
            notification_emails: is_null($defaultEmail) ? $userEmails[$metaRow->userid] : implode(',', [$defaultEmail, $userEmails[$metaRow->userid]]),
            publish_down: $end,
            registration_access: $this->registered_acl,
            registration_start_date: $this->registration_start_date,
            title: $title,
            created_by: $userId, // Set to the therapist uid for reporting access
            location_id: $this->eventInfo->ebLocationId,
          )
        );

        $count += $this->HandleResponseMeta($response, $packageInfo, $metaKey);
      }
    }

    $this->Log("Deployed $count spa packages.");

    return true;
  }

  protected function formatAlias(array $in): string
  {
    return strtolower(preg_replace(
      '/[^\S]+/',
      '_',
      implode('-', [$this->eventInfo->prefix, 'spa', ...$in])
    ));
  }
}
