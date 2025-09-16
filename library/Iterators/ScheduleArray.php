<?php

/**
 * @package     ClawCorpLib
 * @subpackage  com_claw
 *
 * @copyright   (C) 2025 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace ClawCorpLib\Iterators;

use ClawCorpLib\Lib\ScheduleRecord;

class ScheduleArray implements \IteratorAggregate, \ArrayAccess, \Countable
{
  private $schedules = [];

  public function __construct(ScheduleRecord ...$schedules)
  {
    $this->schedules = $schedules;
  }

  public function count(): int
  {
    return count($this->schedules);
  }

  public function get(int $index): ?ScheduleRecord
  {
    return $this->schedules[$index] ?? null;
  }

  public function set(int $index, ScheduleRecord $schedule): void
  {
    $this->schedules[$index] = $schedule;
  }

  public function offsetExists($offset): bool
  {
    return isset($this->schedules[$offset]);
  }

  public function offsetGet($offset): ?ScheduleRecord
  {
    return $this->schedules[$offset] ?? null;
  }

  public function offsetSet($offset, $value): void
  {
    if (!($value instanceof ScheduleRecord)) {
      throw new \InvalidArgumentException('Value must be of type schedule');
    }

    if ($offset === null) {
      $this->schedules[] = $value;
    } else {
      $this->schedules[$offset] = $value;
    }
  }

  public function offsetUnset($offset): void
  {
    unset($this->schedules[$offset]);
  }

  public function getIterator(): \Traversable
  {
    return new \ArrayIterator($this->schedules);
  }
}
