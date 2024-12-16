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

use ClawCorpLib\Grid\GridShift;

class GridShiftArray implements \IteratorAggregate, \ArrayAccess, \Countable
{
  private $gridShifts = [];

  public function __construct(GridShift ...$gridShifts)
  {
    $this->gridShifts = $gridShifts;
  }

  public function count(): int
  {
    return count($this->gridShifts);
  }

  public function get(int|string $index): ?GridShift
  {
    return $this->gridShifts[$index] ?? null;
  }

  public function set(int|string $index, GridShift $gridShift): void
  {
    $this->gridShifts[$index] = $gridShift;
  }

  public function offsetExists($offset): bool
  {
    return isset($this->gridShifts[$offset]);
  }

  public function offsetGet($offset): ?GridShift
  {
    return $this->gridShifts[$offset] ?? null;
  }

  public function offsetSet($offset, $value): void
  {
    if (!($value instanceof GridShift)) {
      throw new \InvalidArgumentException('Value must be of type GridShift');
    }

    if ($offset === null) {
      $this->gridShifts[] = $value;
    } else {
      $this->gridShifts[$offset] = $value;
    }
  }

  public function offsetUnset($offset): void
  {
    unset($this->gridShifts[$offset]);
  }

  public function getIterator(): \Traversable
  {
    yield from $this->gridShifts;
  }

  // Custom to mimic array_keys()
  public function keys(): array
  {
    return array_keys($this->gridShifts);
  }
}
