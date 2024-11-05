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

use ClawCorpLib\Lib\PackageInfo;

class PackageInfoArray implements \IteratorAggregate, \ArrayAccess
{
  private $packageInfos = [];

  public function __construct(PackageInfo ...$packageInfo)
  {
    $this->packageInfos = $packageInfo;
  }

  public function get(int $index): ?PackageInfo
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

  public function offsetGet($offset): ?PackageInfo
  {
    return $this->packageInfos[$offset] ?? null;
  }

  public function offsetSet($offset, $value): void
  {
    if (!($value instanceof PackageInfo)) {
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
    yield from $this->packageInfos;
  }
}
