<?php

/**
 * @package     ClawCorpLib
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorpLib\Lib;

use ClawCorpLib\Enums\EventTypes;
use ClawCorpLib\Lib\EventConfig;
use ClawCorpLib\Lib\EventInfo;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\User\UserFactoryInterface;
use ClawCorpLib\EbInterface\EbEventTable;
use ClawCorpLib\EbInterface\EbRouting;


\defined('_JEXEC') or die;

class Ebmgmt
{
  public $ebEventColumns = [];
  private EbEventTable $eventTable;
  /** @var \Joomla\Database\DatabaseDriver */
  private $db;

  function __construct(
    public EventInfo $eventInfo,
    public int $mainCategoryId,
    public string $itemAlias,
    public string $title,
    public string $description = '',
    public int $created_by = 0,
  ) {
    $this->db = Factory::getContainer()->get('DatabaseDriver');

    $this->initializeEventTable();
    $this->populateDefaults();
  }

  private function populateDefaults()
  {
    $oldOrdering = $this->eventTable->ordering;

    $this->eventTable->alias = $this->itemAlias;
    $this->eventTable->description = $this->description;
    $this->eventTable->short_description = $this->description;
    $this->eventTable->main_category_id = $this->mainCategoryId;
    $this->eventTable->ordering = $this->getOrdering();
    $this->eventTable->title = $this->title;

    if ($this->created_by > 0) {
      $user = Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById($this->created_by);
      if (is_null($user)) {
        throw new \Exception("Invalid created_by user id $this->created_by requested");
      }
    } else {
      $identity = Factory::getApplication()->getIdentity();

      if (is_null($identity)) {
        throw new \Exception("Unauthorized");
      }

      $this->created_by = $identity->id;

      if (!$this->created_by) {
        throw new \Exception("Unauthorized");
      }
    }

    $this->eventTable->created_by = $this->created_by;
    if ($oldOrdering) $this->eventTable->ordering = $oldOrdering;
  }

  public function load(int $id)
  {
    $this->eventTable = EbEventTable::load($id);

    $oldOrdering = $this->eventTable->ordering;
    $this->populateDefaults();
    $this->eventTable->ordering = $oldOrdering;
  }

  public function update()
  {
    $this->eventTable->update();
    EbRouting::updateRoutingTables($this->eventTable, $this->eventInfo);
  }

  public function insert(): int
  {
    $eventId = $this->eventTable->insert();
    EbRouting::updateRoutingTables($this->eventTable, $this->eventInfo);

    return $eventId;
  }

  /**
   * Sets a database column value, defaults to quoting value (passthrough to EbEventTable)
   * @param string $key Column name
   * @param mixed $value Value to set
   */
  public function set(string $key, mixed $value): void
  {
    $this->eventTable->$key = $value;
  }

  /**
   * Gets a database column value (passthrough to EbEventTable)
   * @param $key Column name
   * @return mixed Column Value
   */
  public function get(string $key): mixed
  {
    return $this->eventTable->$key;
  }

  private function getOrdering(): int
  {
    $query = "SELECT MAX(ordering) FROM `#__eb_events` WHERE 1";
    $this->db->setQuery($query);
    return $this->db->loadResult() + 1;
  }

  /**
   * Establishes default values for a new event row. Will die if schema for #__eb_events
   * is not met. This is to protect against future updates to the events schema. Call
   * set() to provide values prior to insert().
   */
  private function initializeEventTable(): void
  {
    // Load from global config, defaults to clean Joomla group install
    $componentParams = ComponentHelper::getParams('com_claw');
    // These params are actually ACL ids, not group ids
    $gid_public = $componentParams->get('packaginfo_public_group', 1);
    $gid_registered = $componentParams->get('packaginfo_registered_group', 14);

    $this->eventTable = new EbEventTable($gid_public, $gid_registered);
  }

  public static function rebuildEventIdMapping()
  {
    /** @var \Joomla\Database\DatabaseDriver */
    $db = Factory::getContainer()->get('DatabaseDriver');

    $aliases = EventConfig::getActiveEventAliases();
    $dates = [];

    foreach ($aliases as $alias) {
      $info = new EventInfo($alias);

      if (EventTypes::refunds == $info->eventType) continue;

      $dates[$alias] = (object)[
        'start' => $info->start_date->toSql(),
        'end' => $info->end_date->toSql()
      ];
    }

    // Load all event rows
    $query = $db->getQuery(true);
    $query->select(['id', 'event_date', 'event_end_date'])
      ->from('#__eb_events')
      ->where('published = 1')
      ->order('id');
    $db->setQuery($query);
    $ebEvents = $db->loadObjectList('id');

    try {
      $db->transactionStart(true);

      $db->truncateTable('#__claw_eventid_mapping');

      foreach ($ebEvents as $e) {
        if ($e->event_date == '0000-00-00 00:00:00') continue;

        foreach ($dates as $alias => $date) {
          if ($e->event_date >= $date->start && $e->event_date <= $date->end) {
            $query = $db->getQuery(true);
            $query
              ->insert($db->quoteName('#__claw_eventid_mapping'))
              ->columns($db->quoteName(['eventid', 'alias']))
              ->values(implode(',', (array)$db->quote([$e->id, $alias])));
            $db->setQuery($query);
            $db->execute();

            break;
          }
        }
      }

      $db->transactionCommit();
    } catch (\Exception $e) {
      $db->transactionRollback();
      throw $e;
    }
  }
}
