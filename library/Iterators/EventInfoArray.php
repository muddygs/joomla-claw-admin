<?php

/**
 * @package     ClawCorpLib
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace ClawCorpLib\Iterators;

use ClawCorpLib\Lib\EventInfo;

class EventInfoArray implements \IteratorAggregate, \ArrayAccess, \Countable
{
  private $eventInfos = [];

  public function __construct(EventInfo ...$eventInfos)
  {
    $this->eventInfos = $eventInfos;
  }

  public function count(): int
  {
    return count($this->eventInfos);
  }

  public function get(int|string $index): ?EventInfo
  {
    return $this->eventInfos[$index] ?? null;
  }

  public function set(int|string $index, EventInfoArray $sponsors): void
  {
    $this->eventInfos[$index] = $sponsors;
  }

  public function offsetExists($offset): bool
  {
    return isset($this->eventInfos[$offset]);
  }

  public function offsetGet($offset): ?EventInfo
  {
    return $this->eventInfos[$offset] ?? null;
  }

  public function offsetSet($offset, $value): void
  {
    if (!($value instanceof EventInfo)) {
      throw new \InvalidArgumentException('Value must be of type EventInfo');
    }

    if ($offset === null) {
      $this->eventInfos[] = $value;
    } else {
      $this->eventInfos[$offset] = $value;
    }
  }

  public function offsetUnset($offset): void
  {
    unset($this->eventInfos[$offset]);
  }

  public function getIterator(): \Traversable
  {
    yield from $this->eventInfos;
  }

  // Custom to mimic array_keys()
  public function keys(): array
  {
    return array_keys($this->eventInfos);
  }
}
