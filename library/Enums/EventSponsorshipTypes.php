<?php

/**
 * @package     ClawCorpLib
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorpLib\Enums;

enum EventSponsorshipTypes: int
{
  case advertising = 1;
  case logo = 2;
  case master_sustaining = 3;
  case black = 4;
  case blue = 5;
  case gold = 6;
  case community = 7;

  public function toString(): string
  {
    return match ($this) {
      EventSponsorshipTypes::advertising => 'Advertising',
      EventSponsorshipTypes::logo => 'Logo Placements',
      EventSponsorshipTypes::master_sustaining => 'Master/Sustaining',
      EventSponsorshipTypes::black => 'Black-Level',
      EventSponsorshipTypes::blue => 'Blue-Level',
      EventSponsorshipTypes::gold => 'Gold-Level',
      EventSponsorshipTypes::community => 'Community',
    };

    throw (new \Exception("Unhandled PackageInfoTypes value: $this->value"));
  }

  public static function toOptions(): array
  {
    $result = [];

    foreach (EventSponsorshipTypes::cases() as $c) {
      $result[$c->value] = $c->toString();
    }

    // Sort by value, but retain the original key
    asort($result);

    return $result;
  }
}
