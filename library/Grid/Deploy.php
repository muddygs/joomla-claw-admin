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
use ClawCorpLib\Lib\ClawEvents;
use ClawCorpLib\Lib\Ebmgmt;
use ClawCorpLib\Lib\EventInfo;
use ClawCorpLib\Enums\EbPublishedState;
use Joomla\CMS\Factory;

\defined('_JEXEC') or die;

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

  public function __construct(
    private EventInfo $eventInfo,
    private bool $repair = false
  ) {
    $this->db = Factory::getContainer()->get('DatabaseDriver');
    $this->shifts = new GridShiftArray();
    $this->keys = Helpers::getDays();
    $this->baseUnixTime = $this->eventInfo->start_date->toUnix();
    $this->aliasPrefix = $this->eventInfo->shiftPrefix;
    $this->cut_off_date = $this->eventInfo->start_date->toSql();
    $this->location = $this->eventInfo->ebLocationId;
  }

  public function createEvents()
  {
    date_default_timezone_set($this->eventInfo->timezone);

    self::populateShifts();

    $shiftCategoryIds = [...$this->eventInfo->eb_cat_shifts, ...$this->eventInfo->eb_cat_supershifts];
    $shiftRawCategories = ClawEvents::getRawCategories($shiftCategoryIds);

    /** @var \ClawCorpLib\Grid\GridShift */
    foreach ($this->shifts as $shift) {
      if (!array_key_exists($shift->category, $shiftRawCategories)) {
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

    $title = implode(' ', [$this->eventInfo->prefix, $title, "($stitle-$etitle)"]);
    $stars = self::getWeightPrefix($gridTime->weight);

    $description = implode('<br/>', [$shift->description, $shift->requirements]);

    $insert = new Ebmgmt(
      eventAlias: $this->eventInfo->alias,
      mainCategoryId: $shift->category,
      itemAlias: $alias,
      title: $stars . $title,
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

    if ($this->repair && $eventid != 0) {
      $insert->update('id', $eventid);
    } else {
      $eventid = $insert->insert();
      $gridTime->setEventId($key, $eventid);
      $gridTime->save();
    }

    return [$eventid, $title, $stitle, $etitle, $need, $gridTime->weight];
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
      $gridShift = new GridShift($gid, $this->eventInfo);
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
      preg_replace('/[^A-Za-z0-9]+/', '_', $title),
      $sid,
      $tid,
      $weight,
      $key
    ]));
  }

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

  /**
   * Reverses encoding from createAlias
   * @param string $shiftAlias Encoded shift alias
   *
   * @return object Alias components (@see \ClawCorpLib\Grid\Deploy::createAlias)
   */
  public static function parseAlias(string $shiftAlias): object
  {
    $parts = explode('-', $shiftAlias);
    if (count($parts) != 6) {
      throw new \InvalidArgumentException('A shift alias must have 6 components');
    }

    return (object)[
      'prefix' => $parts[0],
      'title' => $parts[1],
      'sid' => $parts[2],
      'tid' => $parts[3],
      'weight' => $parts[4],
      'key' => $parts[5],
    ];
  }
}
