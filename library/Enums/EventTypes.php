<?php

/**
 * @package     ClawCorpLib
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorpLib\Enums;

enum EventTypes: int
{
  case none = 0;
  case main = 1;
  case vc = 3;
  case refunds = 4;
  case single = 5;

  public function toString(): string
  {
    return match ($this) {
      EventTypes::main => 'Main',
      EventTypes::vc => 'Virtual Claw',
      EventTypes::refunds => 'Refunds',
      EventTypes::single => 'Single',
      default => 'None'
    };
  }

  public static function toOptions(): array
  {
    $result = [];

    foreach (EventTypes::cases() as $c) {
      if ($c == EventTypes::refunds) continue;

      $result[$c->value] = $c->toString();
    }

    return $result;
  }
}
