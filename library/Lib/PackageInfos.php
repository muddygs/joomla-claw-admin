<?php

declare(strict_types=1);

namespace ClawCorpLib\Lib;

class PackageInfos implements \IteratorAggregate, \ArrayAccess
{
  private $packageInfos = [];

  public function __construct(PackageInfo ...$packageInfo)
  {
    $this->packageInfos = $packageInfo;
  }

  public function get(int $index): PackageInfo
  {
    return $this->packageInfos[$index] ?? null;
  }

  public function set(int $index, PackageInfo $packageInfo): void
  {
    $this->packageInfos[$index] = $packageInfo;
  }

  public function offsetExists($offset): bool
  {
    return isset($this->packageInfos[$offset]);
  }

  public function offsetGet($offset): PackageInfo
  {
    return $this->packageInfos[$offset] ?? null;
  }

  public function offsetSet($offset, $value): void
  {
    if ( !($value instanceof PackageInfo) ) {
      throw new \InvalidArgumentException('Value must be of type PackageInfo');
    }

    if ($offset === null) {
      $this->packageInfos[] = $value;
    } else {
      $this->packageInfos[$offset] = $value;
    }
  }

  public function offsetUnset($offset): void
  {
    unset($this->packageInfos[$offset]);
  }

  public function getIterator(): \Traversable
  {
    //return new \ArrayIterator($this->packageInfos);
    yield from $this->packageInfos;
  }
}
