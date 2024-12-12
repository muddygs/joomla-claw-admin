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

use ClawCorpLib\Grid\GridTime;

class GridTimeArray implements \IteratorAggregate, \ArrayAccess, \Countable
{
  private $gridTimes = [];

  public function __construct(GridTime ...$gridTimes)
  {
    $this->gridTimes = $gridTimes;
  }

  public function count(): int
  {
    return count($this->gridTimes);
  }

  public function get(int|string $index): ?GridTime
  {
    return $this->gridTimes[$index] ?? null;
  }

  public function set(int|string $index, GridTime $gridTime): void
  {
    $this->gridTimes[$index] = $gridTime;
  }

  public function offsetExists($offset): bool
  {
    return isset($this->gridTimes[$offset]);
  }

  public function offsetGet($offset): ?GridTime
  {
    return $this->gridTimes[$offset] ?? null;
  }

  public function offsetSet($offset, $value): void
  {
    if (!($value instanceof GridTime)) {
      throw new \InvalidArgumentException('Value must be of type GridTime');
    }

    if ($offset === null) {
      $this->gridTimes[] = $value;
    } else {
      $this->gridTimes[$offset] = $value;
    }
  }

  public function offsetUnset($offset): void
  {
    unset($this->gridTimes[$offset]);
  }

  public function getIterator(): \Traversable
  {
    yield from $this->gridTimes;
  }

  // Custom to mimic array_keys()
  public function keys(): array
  {
    return array_keys($this->gridTimes);
  }
}
