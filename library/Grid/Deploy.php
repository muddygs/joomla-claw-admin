<?php

/**
 * @package     ClawCorpLib
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorpLib\Grid;

// This class enforces data format for received form data

use ClawCorpLib\Helpers\Helpers;
use Joomla\Database\DatabaseDriver;
use ClawCorpLib\Lib\EventInfo;
use ClawCorpLib\Enums\EbPublishedState;
use Joomla\CMS\Factory;
use ClawCorpLib\Deploy\EbSyncItem;
use ClawCorpLib\Deploy\EbSync;
use ClawCorpLib\EbInterface\EbEventTable;
use Joomla\CMS\Date\Date;
use ClawCorpLib\Helpers\Config;
use ClawCorpLib\Traits\PackageDeploy;

\defined('JPATH_PLATFORM') or die;

class Deploy
{
  use PackageDeploy;

  private GridShifts $liveShifts;
  private GridShifts $deployedShifts;
  private DatabaseDriver $db;

  private int $baseUnixTime;
  private string $aliasPrefix;
  private Date $cut_off_date;
  private Date $registration_start_date;
  private array $keys;
  private int $location;
  private int $registered_acl = 0;
  private array $log = [];

  private array $unpublishedEventIds = [];

  /**
   * Wrapper class for deploying Shifts and their Grids
   *
   * @param EventInfo $EventInfo
   */
  public function __construct(
    private EventInfo $eventInfo,
  ) {
    $this->db = Factory::getContainer()->get('DatabaseDriver');
    date_default_timezone_set($this->eventInfo->timezone);

    $this->liveShifts = new GridShifts($this->eventInfo);

    $this->ensureDeployedTable(GridShift::SHIFTS_TABLE);
    $this->deployedShifts = new GridShifts($this->eventInfo, true);

    $this->keys = Helpers::days;
    $this->baseUnixTime = $this->eventInfo->start_date->toUnix();
    $this->aliasPrefix = $this->eventInfo->shiftPrefix;
    $this->cut_off_date = $this->eventInfo->start_date;
    $this->location = $this->eventInfo->ebLocationId;
    $this->registration_start_date = Factory::getDate('now', $this->eventInfo->timezone);
    $this->registered_acl = Config::getGlobalConfig('packageinfo_registered_acl', 0);
  }

  protected function getDb(): DatabaseDriver
  {
    return $this->db;
  }

  /**
   * Method to execute shift deployment to Event Booking
   * Outer loop is shifts, inner loop is times
   */
  public function createEvents(): array //logs
  {
    $this->db->transactionStart();

    $liveKeys = $this->liveShifts->shifts->keys();
    $deployedKeys = $this->deployedShifts->shifts->keys();

    $commonKeys = array_unique(array_merge($liveKeys, $deployedKeys), SORT_NUMERIC);
    $deletedKeys = array_diff($deployedKeys, $liveKeys);

    $shiftCategoryIds = [...$this->eventInfo->eb_cat_shifts, ...$this->eventInfo->eb_cat_supershifts];

    try {
      foreach ($commonKeys as $shiftId) {
        $liveShift = $this->liveShifts->shifts[$shiftId];
        $deployedShift = in_array($shiftId, $this->deployedShifts->shifts->keys()) ? $this->deployedShifts->shifts[$shiftId] : null;

        if (!in_array($liveShift->category, $shiftCategoryIds)) {
          throw new \Exception('Invalid category id for ' . $liveShift->title . '. Did you forget to add to the event info config?');
        }

        // TODO: add a hashing function to further minimize processing requirements

        // Add to the unpublish list and sync
        if (
          !is_null($deployedShift) &&
          $liveShift->published != EbPublishedState::published &&
          $deployedShift->published == EbPublishedState::published
        ) {
          // Unpublish deployed gridtimes associated with this shift and sync
          /** @var \ClawCorpLib\Grid\GridTime */
          foreach ($deployedShift->getTimes() as $deployedTime) {
            $eventIds = $deployedTime->getEventIds();
            $this->unpublishedEventIds = array_merge($this->unpublishedEventIds, $eventIds);
          }
        }

        $liveShift->save(false);
      }

      /** @var \ClawCorpLib\Grid\GridTime */
      foreach ($liveShift->getTimes() as $gridTime) {
        foreach ($this->keys as $key) {
          $log = self::upsertEvent($liveShift, $gridTime, $key, $liveShift->published);
          if (!is_null($log)) $this->log[] = $log;
        }
      }
    } catch (\Exception $e) {
      $this->log[] = [$e->getMessage(), '', '', '', '', ''];
      $this->db->transactionRollback();
      return $this->log;
    }

    $this->db->transactionCommit();

    // Cleanup deleted shifts
    // TODO: logging here?
    foreach ($deletedKeys as $id) {
      $this->DeleteDeployedShift($this->deployedShifts->shifts[$id]);
    }

    $unpub = array_unique($this->unpublishedEventIds);
    $unpub = array_filter($unpub, function ($v) {
      $v != 0;
    });

    foreach ($unpub as $eventId) {
      $log = EbEventTable::updatePublishedState($eventId, EbPublishedState::any);
      $this->log[] = [$log, '', '', '', '', ''];
    }

    return $this->log;
  }

  private function SyncEvent(
    EbSyncItem $item,
  ): \ClawCorpLib\Deploy\EbSyncResponse {
    $sync = new EbSync($this->eventInfo, $item);
    return $sync->upsert($item);
  }

  private function DeleteDeployedShift(GridShift $shiftToDelete)
  {
    $deployedTimes = [];

    /** @var \ClawCorpLib\Grid\GridTime */
    foreach ($shiftToDelete->getTimes() as $time) {
      $eventIds = $time->getEventIds();
      $deployedTimes[] = $time->id;

      foreach ($eventIds as $id) {
        try {
          EbEventTable::updatePublishedState($id, EbPublishedState::any);
        } catch (\Exception) {
          # ignore unpublish errors
        }
      }
    }

    $query = $this->db->createQuery()
      ->delete(self::getDeployedTableName(GridShift::SHIFTS_TABLE))
      ->where("id = :id")
      ->bind(':id', $shiftId);
    $this->db->setQuery($query)->execute();

    $table = self::getDeployedTableName(GridShift::SHIFTS_TIMES_TABLE);
    // make safe
    $ids = array_map('intval', $deployedTimes);
    $list = implode(',', $ids);
    $sql = "DELETE FROM $table WHERE id IN ($list)";
    $this->db->setQuery($sql)->execute();
  }

  private function upsertEvent(GridShift $shift, GridTime $gridTime, string $key, EbPublishedState $published): ?array
  {
    $title = ucwords($shift->title);
    $need = $gridTime->getNeeds()[$key];
    $eventId = $gridTime->getEventIds()[$key];

    if ($need == 0 && $eventId == 0) {
      return null;
    }

    // No needs? Make sure it's unpublished
    if ($need == 0) $published = EbPublishedState::any;

    $btime = $this->baseUnixTime + (array_search($key, $this->keys) + 1) * 86400; // seconds in a day
    $offset = Helpers::timeToSeconds($gridTime->time->format('H:i'));

    if ($offset === false) {
      throw new \Exception('Unable to convert ' . $gridTime->time . ' to a time');
    }

    $stime = $btime + $offset;
    $etime = $stime + $gridTime->length * 60 * 60;

    $s = new Date($stime); // Joomla handles numerics as unixtime
    $stitle = date('D h:iA', $stime);

    $e = new Date($etime);
    $etitle = date('D h:iA', $etime);

    $alias = self::createAlias(
      $this->aliasPrefix,
      preg_replace('/[^A-Za-z0-9]+/', '_', $title),
      $shift->id,
      $gridTime->id,
      $gridTime->weight,
      $key
    );

    $weight = "[x{$gridTime->weight}]";

    $title = implode(' ', [$this->eventInfo->prefix, $title . $weight, "($stitle-$etitle)"]);

    $description = implode('<br/>', [$shift->description, $shift->requirements]);

    $response = $this->SyncEvent(
      new EbSyncItem(
        eventInfo: $this->eventInfo,
        id: $eventId,
        published: $published->value,
        main_category_id: $shift->category,
        alias: $alias,
        title: $title,
        description: $description,
        article_id: $this->eventInfo->termsArticleId,
        cancel_before_date: $s,
        cut_off_date: $this->cut_off_date,
        event_date: $s,
        event_end_date: $e,
        publish_down: $e,
        individual_price: 0,
        registration_start_date: $this->registration_start_date,
        registration_access: $this->registered_acl,
        event_capacity: $need,
        enable_cancel_registration: 0,
        location_id: $this->location,
        third_reminder_frequency: 'h', // defaults are 'd'
        send_first_reminder: 14,
        send_second_reminder: 7,
        send_third_reminder: 2,
      )
    );

    if ($response->action == 'noop') {
      if ($eventId) {
        $gridTime->setEventId($key, $eventId);
        $gridTime->save(false);
      } else {
        $gridTime->SyncToDeployed(); // normally called at end of save()
      }

      if ($published != EbPublishedState::published)
        $this->unpublishedEventIds[] = $eventId;

      return null;
    } elseif ($response->action == 'update') {
      var_dump($response->changes);
      $gridTime->setEventId($key, $response->id);
      $gridTime->save();
    } else {
      $gridTime->setEventId($key, $response->id);
      $gridTime->save();
    }

    if ($published != EbPublishedState::published)
      $this->unpublishedEventIds[] = $eventId;

    return [$response->id . " ($response->action)", $title, $stitle, $etitle, $need, $gridTime->weight];
  }

  /**
   * Since Event Booking lacks custom fields, use the alias to encode
   * information regarding the shift
   * @param string $prefix Event prefix
   * @param string $title Event title
   * @param int $sid GridShift ID
   * @param int $tid GridTime ID 
   * @param int $weight Shift weight
   * @param string $key GridTime key (usually day)
   *
   * @return string Encoded alias
   */
  public static function createAlias(string $prefix, string $title, int $sid, int $tid, int $weight, string $key): string
  {
    return strtolower(implode('-', [
      $prefix,
      preg_replace('/[^A-Za-z0-9]+/', '', $title),
      $sid,
      $tid,
      $weight,
      $key
    ]));
  }

  /**
   * Reverses encoding from createAlias
   * @param string $shiftAlias Encoded shift alias
   *
   * @return object Alias components (@see \ClawCorpLib\Grid\Deploy::createAlias)
   */
  public static function parseAlias(EventInfo $eventInfo, string $shiftAlias): object
  {
    // Remove prefix
    if (!str_starts_with($shiftAlias, $eventInfo->shiftPrefix . '-')) {
      throw new \InvalidArgumentException('A shift alias must start with the event shift prefix');
    }

    // Extract old-style prefixes (and new!) components
    preg_match('/(.*?)-(\w+)-(\d+)-(.*)/', $shiftAlias, $matches);

    $parts = explode('-', $matches[4]);

    switch (count($parts)) {
      case 2:
        $tid = $parts[0];
        $weight = 1;
        $key = $parts[1];
        break;
      case 3:
        $tid = $parts[0];
        $weight = $parts[1];
        $key = $parts[2];
        break;
      default:
        throw new \InvalidArgumentException('A shift alias must have 5 or 6 components with prefix:' . $shiftAlias);
        break;
    }

    return (object)[
      'prefix' => $matches[1],
      'title' => $matches[2],
      'sid' => $matches[3],
      'tid' => $tid,
      'weight' => $weight,
      'key' => $key,
    ];
  }
}
