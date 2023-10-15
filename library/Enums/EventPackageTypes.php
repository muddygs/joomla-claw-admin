<?php

namespace ClawCorpLib\Enums;

enum EventPackageTypes: int
{
  case none = 0;
  case attendee = 1;
  case volunteer1 = 2;
  case claw_staff = 3; // Multiple list values
  case event_staff = 4; // Multiple list values
  case event_talent = 5; // Renamed to recruited, Multiple list values
  case vendor_crew = 6;
  case vendor_crew_extra = 26;
  case dinner = 7;
  case brunch_fri = 8;
  case brunch_sat = 22;
  case brunch_sun = 23;
  case buffet_wed = 21;
  case buffet_thu = 9;
  case buffet_fri = 10;
  case buffet_bluf = 30;
  case buffet_sun = 11;
  case meal_combo_all = 24;
  case meal_combo_dinners = 25;
  case volunteer2 = 12;
  case volunteer3 = 13;
  case volunteersuper = 19;
  case educator = 14;
  case day_pass_fri = 15;
  case day_pass_sat = 16;
  case day_pass_sun = 17;
  case pass = 18;
  case vip = 20;

  // Additional options for registration options
  case addons = 27;
  case vip2 = 28;

  // Virtual CLAW
  case virtual_claw = 29;

  public function toString(): string
  {
    return match ($this) {
      EventPackageTypes::none => 'None',
      EventPackageTypes::attendee => 'Attendee',
      EventPackageTypes::claw_staff => 'Coordinator',
      EventPackageTypes::event_staff => 'Staff',
      EventPackageTypes::event_talent => 'Volunteer',
      EventPackageTypes::vendor_crew => 'Vendor Crew',
      EventPackageTypes::vendor_crew_extra => 'Vendor Crew',
      EventPackageTypes::volunteer1 => 'Volunteer',
      EventPackageTypes::volunteer2 => 'Volunteer',
      EventPackageTypes::volunteer3 => 'Volunteer',
      EventPackageTypes::volunteersuper => 'Volunteer',
      EventPackageTypes::educator => 'Educator',
      EventPackageTypes::day_pass_fri => 'Day Pass',
      EventPackageTypes::day_pass_sat => 'Day Pass',
      EventPackageTypes::day_pass_sun => 'Day Pass',
      EventPackageTypes::pass => 'Pass',
      EventPackageTypes::vip => 'VIP',
      EventPackageTypes::virtual_claw => 'Virtual CLAW',
      default => ''
    };
  }

  public static function FindValue(int $key): EventPackageTypes {
    foreach (EventPackageTypes::cases() as $c )
    {
      if ( $c->value == $key ) return $c;
    }

    throw(new \Exception("Invalid EventPackageTypes value: $key"));
  }

}
