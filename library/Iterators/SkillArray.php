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

use ClawCorpLib\Skills\Skill;

class SkillArray implements \IteratorAggregate, \ArrayAccess, \Countable
{
  private $skills = [];

  public function __construct(Skill ...$skills)
  {
    $this->skills = $skills;
  }

  public function count(): int
  {
    return count($this->skills);
  }

  public function get(int|string $index): ?Skill
  {
    return $this->skills[$index] ?? null;
  }

  public function set(int|string $index, Skill $skill): void
  {
    $this->skills[$index] = $skill;
  }

  public function offsetExists($offset): bool
  {
    return isset($this->skills[$offset]);
  }

  public function offsetGet($offset): ?Skill
  {
    return $this->skills[$offset] ?? null;
  }

  public function offsetSet($offset, $value): void
  {
    if (!($value instanceof Skill)) {
      throw new \InvalidArgumentException('Value must be of type Skill');
    }

    if ($offset === null) {
      $this->skills[] = $value;
    } else {
      $this->skills[$offset] = $value;
    }
  }

  public function offsetUnset($offset): void
  {
    unset($this->skills[$offset]);
  }

  public function getIterator(): \Traversable
  {
    yield from $this->skills;
  }

  // Custom to mimic array_keys()
  public function keys(): array
  {
    return array_keys($this->skills);
  }
}
