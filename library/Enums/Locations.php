<?php

/**
 * @package     ClawCorpLib
 * @subpackage  com_claw
 *
 * @copyright   (C) 2025 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorpLib\Enums;

enum Locations: int
{
  case cleveland = 1;
  case losangeles = 2;
  case other = 3;

  public function toString(): string
  {
    return match ($this) {
      Locations::cleveland => 'Cleveland',
      Locations::losangeles => 'Los Angeles',
      Locations::other => 'Other',
    };

    throw (new \Exception("Unhandled Locations value: $this->value"));
  }

  public function toContentParam(): string
  {
    $param = strtolower($this->toString());
    $param = preg_replace('#[^a-z]#', '', $param);
    return $param;
  }
}
