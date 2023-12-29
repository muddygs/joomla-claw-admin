<?php
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

  public function toString(): string
  {
    return match($this) {
      PackageInfoTypes::none => 'None',
      PackageInfoTypes::main => 'Main',
      PackageInfoTypes::addon => 'Addon',
      PackageInfoTypes::combomeal => 'Combo Meal',
      PackageInfoTypes::speeddating => 'Speed Dating',
      PackageInfoTypes::sponsorship => 'Sponsorship',
      PackageInfoTypes::daypass => 'Day Pass',
      PackageInfoTypes::coupononly => 'Coupon Only',
      PackageInfoTypes::passes => 'Passes',
    };

    throw(new \Exception("Unhandled PackageInfoTypes value: $this->value"));
  }

  public static function FindValue(int $value): PackageInfoTypes {
    foreach (PackageInfoTypes::cases() as $c )
    {
      if ( $c->value == $value ) return $c;
    }

    throw(new \Exception("Invalid PackageInfoTypes value: $value"));
  }

  public static function toOptions(): array
  {
    $result = [];

    foreach ( PackageInfoTypes::cases() as $c ) {
      $result[$c->value] = $c->toString();
    }

    // Sort by value, but retain the original key
    asort($result);

    return $result;
  }

}
