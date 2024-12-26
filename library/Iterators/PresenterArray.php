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

use ClawCorpLib\Skills\Presenter;

class PresenterArray implements \IteratorAggregate, \ArrayAccess, \Countable
{
  private $presenters = [];

  public function __construct(Presenter ...$presenters)
  {
    $this->presenters = $presenters;
  }

  public function count(): int
  {
    return count($this->presenters);
  }

  public function get(int|string $index): ?Presenter
  {
    return $this->presenters[$index] ?? null;
  }

  public function set(int|string $index, Presenter $presenter): void
  {
    $this->presenters[$index] = $presenter;
  }

  public function offsetExists($offset): bool
  {
    return isset($this->presenters[$offset]);
  }

  public function offsetGet($offset): ?Presenter
  {
    return $this->presenters[$offset] ?? null;
  }

  public function offsetSet($offset, $value): void
  {
    if (!($value instanceof Presenter)) {
      throw new \InvalidArgumentException('Value must be of type Presenter');
    }

    if ($offset === null) {
      $this->presenters[] = $value;
    } else {
      $this->presenters[$offset] = $value;
    }
  }

  public function offsetUnset($offset): void
  {
    unset($this->presenters[$offset]);
  }

  public function getIterator(): \Traversable
  {
    yield from $this->presenters;
  }

  // Custom to mimic array_keys()
  public function keys(): array
  {
    return array_keys($this->presenters);
  }
}
