<?php

/**
 * @package     ClawCorpLib
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorpLib\Lib;

use Joomla\CMS\Factory;
use Joomla\CMS\Date\Date;
use ClawCorpLib\Enums\EventTypes;

class EventInfo
{
  const startdayofweek = 1; // Monday

  public string $shiftPrefix = '';
  public string $description;
  public int $clawLocationId;
  public int $ebLocationId;
  public Date $start_date;
  public Date $end_date;
  public string $prefix;
  public Date $cancelBy;
  public string $timezone;
  public bool $active;
  public EventTypes $eventType;
  public bool $onsiteActive;
  public bool $anyShiftSelection;
  public bool $dayPassesActive;
  public bool $passesActive;
  public bool $passesOtherActive;
  public bool $badgePrintingOverride;
  public int $termsArticleId;

  public array $eb_cat_shifts;
  public array $eb_cat_supershifts;
  public array $eb_cat_speeddating;
  public array $eb_cat_equipment;
  public array $eb_cat_sponsorship;
  public array $eb_cat_sponsorships;
  public int $eb_cat_dinners;
  public int $eb_cat_brunches;
  public int $eb_cat_buffets;
  public int $eb_cat_combomeals;
  public array $eb_cat_invoicables;

  /**
   * Event info object with simple date validation if main events are allowed
   * @param string $eventAlias
   * @return void 
   */
  public function __construct(
    public readonly string $alias,
    public readonly bool $withUnpublished = false,
  ) {
    if (!EventInfos::isEventAlias($this->alias, $this->withUnpublished)) {
      throw new \Exception(__FILE__ . ': Event alias not found or not active: ' . $alias);
    }

    $info = $this->loadRawEventInfo($alias);

    // Get server timezone
    $this->timezone = $info->timezone;

    $this->description = $info->description;
    $this->ebLocationId = $info->ebLocationId;
    $this->clawLocationId = $info->clawLocationId;
    $this->start_date = Factory::getDate($info->start_date, $this->timezone);
    $this->end_date = Factory::getDate($info->end_date, $this->timezone);
    $this->prefix = strtoupper($info->prefix);
    $this->cancelBy = Factory::getDate($info->cancelBy, $this->timezone);
    $this->active = $info->active ?? false;
    try {
      $this->eventType = EventTypes::from($info->eventType);
    } catch (\ValueError) {
      throw (new \Exception("Invalid EventTypes value: {$info->eventType}"));
    }
    $this->onsiteActive = $info->onsiteActive ?? false;
    $this->anyShiftSelection = $info->anyShiftSelection ?? false;
    $this->dayPassesActive = $info->dayPassesActive ?? false;
    $this->passesActive = $info->passesActive ?? false;
    $this->passesOtherActive = $info->passesOtherActive ?? false;
    $this->badgePrintingOverride = $info->badge_printing_override ?? true;
    $this->termsArticleId = $info->termsArticleId;

    $this->eb_cat_shifts = json_decode($info->eb_cat_shifts ?? '[]') ?? [];
    $this->eb_cat_supershifts = json_decode($info->eb_cat_supershifts ?? '[]') ?? [];
    $this->eb_cat_speeddating = json_decode($info->eb_cat_speeddating ?? '[]') ?? [];
    $this->eb_cat_equipment = json_decode($info->eb_cat_equipment ?? '[]') ?? [];
    $this->eb_cat_sponsorship = json_decode($info->eb_cat_sponsorship ?? '[]') ?? [];
    $this->eb_cat_sponsorships = json_decode($info->eb_cat_sponsorships ?? '[]') ?? [];
    $this->eb_cat_dinners = $info->eb_cat_dinners ?? 0;
    $this->eb_cat_brunches = $info->eb_cat_brunches ?? 0;
    $this->eb_cat_buffets = $info->eb_cat_buffets ?? 0;
    $this->eb_cat_combomeals = $info->eb_cat_combomeals ?? 0;
    $this->eb_cat_invoicables = json_decode($info->eb_cat_invoicables ?? '[]') ?? [];

    // Date validation:
    // start_date must be a Monday, only if eventType is main
    // this allows refund and virtualclaw to exist in their odd separate way

    if (EventTypes::main == $this->eventType) {
      $this->shiftPrefix = strtolower($this->prefix) . '-shift';

      if ($this->start_date->dayofweek != EventInfo::startdayofweek) {
        var_dump($this);
        die("Event Start Date Must Be: " . EventInfo::startdayofweek . '. Got: ' . $this->start_date->dayofweek);
      }

      $this->end_date->setTime(23, 59, 59);
    }
  }

  private function loadRawEventInfo(string $alias): object
  {
    if (empty($alias)) throw new \Exception(__FILE__ . ': Event alias cannot be empty');

    /** @var \Joomla\Database\DatabaseDriver */
    $db = Factory::getContainer()->get('DatabaseDriver');
    $alias = strtolower($alias);

    $query = $db->getQuery(true);
    $query->select('*')
      ->from('#__claw_eventinfos')
      ->where('alias = :alias')
      ->bind(':alias', $alias);
    $db->setQuery($query);
    return $db->loadObject();
  }

  /**
   * Mimics Date object functionality, returning event start date modified by the modifier
   * @param string $modifier
   * @return Date|bool Modified date 
   */
  public function modify(string $modifier): Date|bool
  {
    // Clone because modify changes the original Date object
    $date = clone $this->start_date;
    $date->setTimezone(new \DateTimeZone('UTC'));

    return $date->modify($modifier);
  }
}
