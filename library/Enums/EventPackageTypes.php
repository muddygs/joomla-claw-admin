<?php

namespace ClawCorpLib\Enums;

enum EventPackageTypes: int
{
  case none = 0;
  case attendee = 1;
  case volunteer1 = 2;
  case claw_staff = 3;
  case claw_board = 32;
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
  case buffet_sat = 31;
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
  case spa = 33;

    // Additional options for registration options
  case addons = 27;
  case vip2 = 28;

    // Virtual CLAW
  case virtual_claw = 29;

    // Combos for c0424 and beyond
    // TODO: Needs better abstraction, meta events are very hacky
  case combo_meal_1 = 100;
  case combo_meal_2 = 101;
  case combo_meal_3 = 102;
  case combo_meal_4 = 103;

  case speeddating = 901;
  case sponsorship = 902;
  case equipment = 903;

  public function toString(): string
  {
    return match ($this) {
      EventPackageTypes::none => 'None',
      EventPackageTypes::attendee => 'Attendee',
      EventPackageTypes::claw_staff => 'Coordinator',
      EventPackageTypes::claw_board => 'Board',
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

  /**
   * Returns the short name for the package type user in friendly links
   * Usually <prefix>-reg-<shortname>
   * @return string
   */
  public function toLink(): string
  {
    return match ($this) {
      EventPackageTypes::attendee => 'att',
      EventPackageTypes::claw_staff => 'claw',
      EventPackageTypes::claw_board => 'board',
      EventPackageTypes::event_staff => 'sta',
      EventPackageTypes::event_talent => 'tal',
      EventPackageTypes::vendor_crew => 'ven',
      EventPackageTypes::vendor_crew_extra => 'ven-extra',
      EventPackageTypes::volunteer2 => 'vol',
      EventPackageTypes::volunteer3 => 'vol3',
      EventPackageTypes::volunteersuper => 'super',
      EventPackageTypes::educator => 'edu',
      EventPackageTypes::vip => 'vip',
      EventPackageTypes::vip2 => 'vip2',
      EventPackageTypes::addons => 'addons',
      default => ''
    };
  }

  public static function FindValue(int $key): EventPackageTypes
  {
    foreach (EventPackageTypes::cases() as $c) {
      if ($c->value == $key) return $c;
    }

    throw (new \Exception("Invalid EventPackageTypes value: $key"));
  }

  public static function toOptions(): array
  {
    $result = [];

    foreach (EventPackageTypes::cases() as $c) {
      if (
        $c == EventPackageTypes::none ||
        $c == EventPackageTypes::vip2 ||
        $c == EventPackageTypes::addons ||
        $c == EventPackageTypes::speeddating ||
        $c == EventPackageTypes::sponsorship
      ) {
        continue;
      }

      // Convert name to string
      $name = $c->name;
      // Remove underscores
      $name = str_replace('_', ' ', $name);
      // Capitalize
      $name = ucwords($name);

      $result[$c->value] = $name;
    }

    // Sort by value, but retain the original key
    asort($result);

    return $result;
  }
}
