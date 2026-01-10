<?php

/**
 * @package     ClawCorpLib
 * @subpackage  com_claw
 *
 * @copyright   (C) 2025 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorpLib\Deploy;

use ClawCorpLib\Enums\EbPublishedState;
use Joomla\Database\DatabaseDriver;
use Joomla\CMS\Factory;
use ClawCorpLib\Lib\EventInfo;
use ClawCorpLib\Lib\Ebmgmt;

final class EbSyncResponse
{
  public function __construct(
    public int $id,
    public string $action,
    public array $changes = [],
  ) {}
}

final class EbSync
{
  private DatabaseDriver $db;

  /** @var callable(string): ?object */
  private $findByAlias;

  public function __construct(
    private EventInfo $eventInfo,
    public EbSyncItem $item,
    ?callable $findByAliasOverride = null,
  ) {
    $this->db = Factory::getContainer()->get('DatabaseDriver');
    $this->findByAlias = $findByAliasOverride ?? [$this, 'findByAliasDefault'];
  }

  public function upsert(): EbSyncResponse
  {
    if (!$this->eventInfo->active) {
      return new EbSyncResponse(
        id: 0,
        action: 'noop'
      );
    }

    $existing = ($this->findByAlias)($this->item->alias);

    if (!$existing) {
      if ($this->item->published != EbPublishedState::published->value) {
        return new EbSyncResponse(
          id: 0,
          action: 'noop'
        );
      }

      // Standard insert
      $newId = $this->insertViaEbmgmt();
      return new EbSyncResponse(
        id: $newId,
        action: 'insert'
      );
    }

    // Determine if changes exist
    $forceSync = false;
    if ($this->item->id == 0) {
      $this->item->id = (int) $existing->id;
      $forceSync = true;
    }

    $changes = $this->diff($existing);

    if (empty($changes) && !$forceSync) {
      return new EbSyncResponse(
        id: $this->item->id,
        action: 'noop'
      );
    }

    $this->updateViaEbmgmt($changes);
    return new EbSyncResponse(
      id: $this->item->id,
      action: 'update',
      changes: $changes
    );
  }

  private function findByAliasDefault(string $alias): ?object
  {
    $q  = $this->db->getQuery(true)
      ->select('*')
      ->from($this->db->quoteName('#__eb_events'))
      ->where($this->db->quoteName('alias') . ' = ' . $this->db->quote($alias));

    $this->db->setQuery($q);
    return $this->db->loadObject();
  }

  private function insertViaEbmgmt(): int
  {
    // Keep your current Ebmgmt usage so all side-effects/validation remain consistent.
    $insert = new Ebmgmt(
      eventInfo: $this->eventInfo,
      mainCategoryId: $this->item->main_category_id,
      itemAlias: $this->item->alias,
      title: $this->item->title,
      description: $this->item->description,
      created_by: $this->item->created_by,
    );

    foreach (get_object_vars($this->item->toSql()) as $col => $val) {
      // Ebmgmt constructor already set some fields; set the rest
      if (in_array($col, ['id', 'main_category_id', 'alias', 'title', 'description', 'created_by'], true)) {
        continue;
      }
      $insert->set($col, $val);
    }

    return $insert->insert();
  }

  // $changes is already prepped for sql
  private function updateViaEbMgmt(array $changes): void
  {
    $update = new Ebmgmt(
      eventInfo: $this->eventInfo,
      mainCategoryId: $this->item->main_category_id,
      itemAlias: $this->item->alias,
      title: $this->item->title,
      description: $this->item->description
    );

    $update->load($this->item->id);

    foreach ($changes as $key => $value) {
      # If you want to change the ownership, use Event Booking's interface
      if ($key == 'created_by') continue;

      try {
        $update->set($key, $value);
      } catch (\Exception $e) {
        dd($changes);
        throw new \InvalidArgumentException("Invalid value within record update: $e");
      }
    }

    $update->update();
  }

  // Strict diff against DB row: compares normalized payload to existing row values.
  private function diff(object $existing): array
  {
    $changes = [];
    $asSql = $this->item->toSql();

    foreach (array_keys(get_object_vars($this->item)) as $name) {
      if ($name == 'created_by') continue;

      if (
        $name == 'registration_start_date' &&
        $existing->registration_start_date < $asSql->registration_start_date &&
        $existing->registration_start_date != $this->db->getNullDate()
      ) continue;

      if (!property_exists($existing, $name)) {
        throw new \InvalidArgumentException("Mismatch between EbSyncItem named $name and #__eb_events columns");
      }

      $dbValue = $existing->$name;

      // Convert strings numerics to specific type
      // We only need int and float
      if (is_int($this->item->$name)) {
        $dbValue = (int)$dbValue;
      }
      if (is_float($this->item->$name)) {
        $dbValue = (float)$dbValue;
      }

      if ($dbValue !== $asSql->$name) {
        $changes[$name] = $asSql->$name;
      }
    }

    return $changes;
  }
}
