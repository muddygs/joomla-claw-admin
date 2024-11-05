<?php

/**
 * @package     ClawCorpLib
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorpLib\Enums;

enum SponsorshipType: int
{
  case None = 0;
  case Sponsor = 1;
  case Sustaining = 2;
  case Legacy_Sustaining = 6;
  case Master = 3;
  case Legacy_Master = 5;
  case Media = 4;

  public function toString(): string
  {
    return match ($this) {
      SponsorshipType::Sponsor => 'Sponsor',
      SponsorshipType::Sustaining => 'Sustaining',
      SponsorshipType::Legacy_Sustaining => 'Legacy Sustaining',
      SponsorshipType::Master => 'Master',
      SponsorshipType::Legacy_Master => 'Legacy Master',
      SponsorshipType::Media => 'Media',
    };
  }

  public static function values(): array
  {
    return array_column(self::cases(), 'value');
  }

  public static function valuesOrdered(): array
  {
    $result = [];
    $result[] = SponsorshipType::Legacy_Master->value;
    $result[] = SponsorshipType::Master->value;
    $result[] = SponsorshipType::Legacy_Sustaining->value;
    $result[] = SponsorshipType::Sustaining->value;
    $result[] = SponsorshipType::Sponsor->value;
    $result[] = SponsorshipType::Media->value;
    return $result;
  }

  public static function FindValue(int $key): SponsorshipType
  {
    foreach (SponsorshipType::cases() as $c) {
      if ($c->value == $key) return $c;
    }

    throw (new \Exception("Invalid SponsorshipType value: $key"));
  }
}
