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
use ClawCorpLib\Enums\EbPublishedState;
use ClawCorpLib\Iterators\EventInfoArray;

class EventInfos implements \IteratorAggregate, \Countable
{
  private EventInfoArray $eventInfoArray;

  public function __construct(
    public bool $fromPlugin = false,
    public bool $withUnpublished = false,
    public int $clawLocationId = 0,
  ) {
    $this->eventInfoArray = new EventInfoArray();
    $this->load();
  }

  #region IteratorAggregate, keys (sub for array_keys), countable

  public function getIterator(): \Traversable
  {
    return $this->eventInfoArray->getIterator();
  }

  public function keys(): array
  {
    return $this->eventInfoArray->keys();
  }

  public function count(): int
  {
    return count($this->eventInfoArray);
  }

  public function offsetUnset($offset): void
  {
    unset($this->eventInfoArray[$offset]);
  }


  #endregion
  #
  private static function loadAliases(bool $withUnpublished = false, int $clawLocationId = 0): array
  {
    /** @var \Joomla\Database\DatabaseDriver */
    $db = Factory::getContainer()->get('DatabaseDriver');

    $db->transactionStart(false);

    $query = $db->getQuery(true);
    $query->select(['alias'])
      ->from('#__claw_eventinfos')
      ->order('end_date DESC');

    if (!$withUnpublished) {
      $query->where('active=' . EbPublishedState::published->value);
    }

    if ($clawLocationId > 0) {
      $query->where('clawLocationId=' . $clawLocationId);
    }

    $db->setQuery($query);
    $aliases = $db->loadColumn();

    // $db->unlockTables();
    $db->transactionCommit();

    return $aliases;
  }

  private function load()
  {
    $aliases = self::loadAliases($this->withUnpublished, $this->clawLocationId);

    foreach ($aliases as $alias) {
      $this->eventInfoArray[strtolower($alias)] = new EventInfo($alias, $this->withUnpublished);
    }
  }

  /**
   * Returns EventInfoArray, indexed by event alias
   * @return EventInfoArray 
   */
  public function get(): EventInfoArray
  {
    return $this->eventInfoArray;
  }

  /**
   * Magic method to get an EventInfo from an event alias
   * @return EventInfo (or null if alias not found)
   */
  public function __get(string $eventAlias): ?EventInfo
  {
    if ($this->eventInfoArray->offsetExists($eventAlias)) {
      return $this->eventInfoArray[$eventAlias];
    }

    return null;
  }

  public static function isEventAlias(string $eventAlias, bool $withUnpublished = false): bool
  {
    $aliases = self::loadAliases($withUnpublished);
    return in_array(strtolower($eventAlias), $aliases);
  }
}
