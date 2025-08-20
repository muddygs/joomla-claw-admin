<?php

/**
 * @package     ClawCorpLib
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorpLib\Enums;

enum PackageInfoTypes: int
{
  case none = 0;
  case main = 1;
  case addon = 2;
  case combomeal  = 3;
  case speeddating = 4;
  case sponsorship = 5;
  case daypass = 6;
  case coupononly = 7;
  case passes = 8;
  case equipment = 9;
  case spa = 10;
  case passes_other = 11;
  case vendormart = 12;

  public function toString(): string
  {
    return match ($this) {
      PackageInfoTypes::none => 'None',
      PackageInfoTypes::main => 'Main',
      PackageInfoTypes::addon => 'Addon',
      PackageInfoTypes::combomeal => 'Combo Meal',
      PackageInfoTypes::speeddating => 'Speed Dating',
      PackageInfoTypes::sponsorship => 'Sponsorship',
      PackageInfoTypes::daypass => 'Day Pass',
      PackageInfoTypes::coupononly => 'Coupon Only',
      PackageInfoTypes::passes => 'Passes',
      PackageInfoTypes::passes_other => 'Passes (Other)',
      PackageInfoTypes::equipment => 'Equipment Rental',
      PackageInfoTypes::spa => 'Spa Session',
      PackageInfoTypes::vendormart => 'VendorMart',
    };

    throw (new \Exception("Unhandled PackageInfoTypes value: $this->value"));
  }

  public static function FindValue(int $value): PackageInfoTypes
  {
    foreach (PackageInfoTypes::cases() as $c) {
      if ($c->value == $value) return $c;
    }

    throw (new \Exception("Invalid PackageInfoTypes value: $value"));
  }

  public static function toOptions(): array
  {
    $result = [];

    foreach (PackageInfoTypes::cases() as $c) {
      $result[$c->value] = $c->toString();
    }

    // Sort by value, but retain the original key
    asort($result);

    return $result;
  }
}
