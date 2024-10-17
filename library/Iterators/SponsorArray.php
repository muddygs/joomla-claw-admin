<?php

declare(strict_types=1);

namespace ClawCorpLib\Iterators;

use ClawCorpLib\Lib\Sponsor;

class SponsorArray implements \IteratorAggregate, \ArrayAccess, \Countable
{
  private $sponsors = [];

  public function __construct(Sponsor ...$sponsors)
  {
    $this->sponsors = $sponsors;
  }

  public function count(): int
  {
    return count($this->sponsors);
  }

  public function get(int $index): ?Sponsor
  {
    return $this->sponsors[$index] ?? null;
  }

  public function set(int $index, Sponsor $sponsor): void
  {
    $this->sponsors[$index] = $sponsor;
  }

  public function offsetExists($offset): bool
  {
    return isset($this->sponsors[$offset]);
  }

  public function offsetGet($offset): ?Sponsor
  {
    return $this->sponsors[$offset] ?? null;
  }

  public function offsetSet($offset, $value): void
  {
    if (!($value instanceof Sponsor)) {
      throw new \InvalidArgumentException('Value must be of type Sponsor');
    }

    if ($offset === null) {
      $this->sponsors[] = $value;
    } else {
      $this->sponsors[$offset] = $value;
    }
  }

  public function offsetUnset($offset): void
  {
    unset($this->sponsors[$offset]);
  }

  public function getIterator(): \Traversable
  {
    yield from $this->sponsors;
  }
}
