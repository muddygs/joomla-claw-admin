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
use ClawCorpLib\Iterators\GridShiftArray;
use Joomla\Database\DatabaseDriver;
use ClawCorpLib\Lib\Ebmgmt;
use ClawCorpLib\Lib\EventInfo;
use ClawCorpLib\Enums\EbPublishedState;
use Joomla\CMS\Factory;

\defined('JPATH_PLATFORM') or die;

class Deploy
{
  private GridShiftArray $shifts;
  private DatabaseDriver $db;

  private int $baseUnixTime;
  private string $aliasPrefix;
  private string $cut_off_date;
  private array $keys;
  private int $location;
  public array $log = [];

  /**
   * Wrapper class for deploying Shifts and their Grids
   *
   * @param EventInfo $EventInfo
   * @param bool $repair Set to true to repair values in deployed events (@see
\ClawCorp\Component\Claw\Administrator\Controller\ShiftsController)
   */
  public function __construct(
    private EventInfo $eventInfo,
    private bool $repair = false
  ) {
    $this->db = Factory::getContainer()->get('DatabaseDriver');
    $this->shifts = new GridShiftArray();
    $this->keys = Helpers::days;
    $this->baseUnixTime = $this->eventInfo->start_date->toUnix();
    $this->aliasPrefix = $this->eventInfo->shiftPrefix;
    $this->cut_off_date = $this->eventInfo->start_date->toSql();
    $this->location = $this->eventInfo->ebLocationId;
  }

  /**
   * Method to execute shift deployment to Event Booking
   */
  public function createEvents()
  {
    date_default_timezone_set($this->eventInfo->timezone);

    self::populateShifts();

    $shiftCategoryIds = [...$this->eventInfo->eb_cat_shifts, ...$this->eventInfo->eb_cat_supershifts];

    /** @var \ClawCorpLib\Grid\GridShift */
    foreach ($this->shifts as $shift) {
      if (!in_array($shift->category, $shiftCategoryIds)) {
        throw new \Exception('Invalid category id for ' . $shift->title . '. Did you forget to add to the event info config?');
      }

      if ($shift->published != EbPublishedState::published) continue;

      /** @var \ClawCorpLib\Grid\GridTime */
      foreach ($shift->getTimes() as $gridTime) {
        $needed = $gridTime->getNeeds();
        $eventIds = $gridTime->getEventIds();

        foreach ($this->keys as $key) {
          if ((!$this->repair && $eventIds[$key] != 0) || $needed[$key] < 1) {
            continue;
          }

          $this->log[] = self::createEvent($shift, $gridTime, $key);
        }
      }
    }
  }

  private function createEvent(GridShift $shift, GridTime $gridTime, string $key): array
  {
    $title = ucwords($shift->title);
    $need = $gridTime->getNeeds()[$key];
    $eventid = $gridTime->getEventIds()[$key];

    $btime = $this->baseUnixTime + (array_search($key, $this->keys) + 1) * 86400; // seconds in a day
    $offset = Helpers::timeToSeconds($gridTime->time->format('H:i'));

    if ($offset === false) {
      throw new \Exception('Unable to convert ' . $gridTime->time . ' to a time');
    }

    $stime = $btime + $offset;
    $etime = $stime + $gridTime->length * 60 * 60;

    $s = date('Y-m-d H:i:s', $stime);
    $stitle = date('D h:iA', $stime);

    $e = date('Y-m-d H:i:s', $etime);
    $etitle = date('D h:iA', $etime);

    $alias = self::createAlias(
      $this->aliasPrefix,
      preg_replace('/[^A-Za-z0-9]+/', '_', $title),
      $shift->id,
      $gridTime->id,
      $gridTime->weight,
      $key
    );

    $weight = $gridTime->weight > 1 ? " [x{$gridTime->weight}]" : '';

    $title = implode(' ', [$this->eventInfo->prefix, $title . $weight, "($stitle-$etitle)"]);
    //$stars = self::getWeightPrefix($gridTime->weight);

    $description = implode('<br/>', [$shift->description, $shift->requirements]);

    $insert = new Ebmgmt(
      eventInfo: $this->eventInfo,
      mainCategoryId: $shift->category,
      itemAlias: $alias,
      title: $title,
      description: $description
    );

    if ($this->repair) {
      $insert->load($eventid);
    }

    $insert->set('location_id', $this->location);
    $insert->set('event_date', $s);
    $insert->set('event_end_date', $e);
    $insert->set('event_capacity', $need);
    $insert->set('cut_off_date', $this->cut_off_date);
    $insert->set('enable_cancel_registration', 0);

    if ($shift->enableNotifications) {
      $this->configureNotifications($insert, $s);
    }

    if ($this->repair && $eventid != 0) {
      $insert->update('id', $eventid);
    } else {
      $eventid = $insert->insert();
      $gridTime->setEventId($key, $eventid);
      $gridTime->save();
    }

    return [$eventid, $title, $stitle, $etitle, $need, $gridTime->weight];
  }

  private function configureNotifications(Ebmgmt $insert)
  {
    // Timetables for notification
    //
    // Two weeks before overall event
    // One week before overall event
    // Two hours before shift

    $insert->set("send_first_reminder", 14);
    $insert->set("first_reminder_frequency", 'd');
    $insert->set("send_second_reminder", 7);
    $insert->set("second_reminder_frequency", 'd');
    $insert->set("send_third_reminder", 2);
    $insert->set("third_reminder_frequency", 'h');
  }

  private function populateShifts(): void
  {
    $eventAlias = $this->eventInfo->alias;

    $query = $this->db->getQuery(true);
    $query->select('id')
      ->from(GridShift::SHIFTS_TABLE)
      ->where('event = :event')
      ->where('published = 1')
      ->bind(':event', $eventAlias)
      ->order('id');
    $this->db->setQuery($query);

    $gridIds = $this->db->loadColumn();

    foreach ($gridIds as $gid) {
      $gridShift = new GridShift($gid);
      $this->shifts[$gid] = $gridShift;
    }
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

  /**
   * Return an HTML entity of inverse circle number (1-10) to indicate weight
   * in event title
   */
  private function getWeightPrefix(int $weight): string
  {
    return match ($weight) {
      1 => '&#10102;',
      2 => '&#10103;',
      3 => '&#10104;',
      4 => '&#10105;',
      5 => '&#10106;',
      6 => '&#10107;',
      7 => '&#10108;',
      8 => '&#10109;',
      9 => '&#10110;',
      10 => '&#10111;',
      default => 'X',
    };
  }
}
