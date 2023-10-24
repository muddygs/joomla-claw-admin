<?php

namespace ClawCorpLib\Events;

defined('_JEXEC') or die('Restricted access');

use ClawCorpLib\Events\AbstractEvent;

use ClawCorpLib\Lib\ClawEvent;
use ClawCorpLib\Lib\ClawEvents;
use ClawCorpLib\Enums\EventTypes;
use ClawCorpLib\Enums\EventPackageTypes;
use ClawCorpLib\Lib\EventInfo;
use stdClass;

class c0424 extends AbstractEvent
{
  public function PopulateInfo(): EventInfo
  {
    return new EventInfo(
      description: 'CLAW 24',
      location: 'Cleveland, OH',
      locationAlias: 'renaissance-cleveland',
      start_date: '2024-04-08 00:00:00', // Monday
      end_date: 'next week Tuesday', // Calculated
      prefix: 'C24',
      shiftPrefix: strtolower('c24-shift-cle-'),
      mainAllowed: true,
      cancelBy: '2024-04-01 00:00:00', // Varies too much to calculate
      timezone: 'America/New_York',
      eventType: EventTypes::main,
      active: true,
      onsiteActive: false,
      termsArticleId: 77
    );
  }

  public function PopulateEvents(string $prefix, $quiet = false)
  {
    $prefix = strtolower($prefix);
    $base = 269;

    #region Meals (do first for meta bundles)
    // Wednesday
    $wed_buffet = $this->AppendEvent(new ClawEvent((object)[
      'alias' => $prefix . '-wed-buffet',
      'description' => 'Staff Dinner Buffet',
      'badgeValue' => 'Wed',
      'eventPackageType' => EventPackageTypes::buffet_wed,
      'isMainEvent' => false,
      'couponValue' => 0,
      'fee' => 50,
      'bundleDiscount' => 30,
      'start' => 'wednesday 5pm',
      'end' => 'wednesday 6:30pm',
      'category' => 'buffet',
      'requiresCoupon' => false,
      'couponAccessGroups' => ['Super Users', 'Administrator'],
      'isAddon' => true
    ], $quiet));

    // Thursday
    $thu_buffet = $this->AppendEvent(new ClawEvent((object)[
      'alias' => $prefix . '-thu-buffet',
      'description' => 'Pig Roast',
      'badgeValue' => 'Thu',
      'eventPackageType' => EventPackageTypes::buffet_thu,
      'isMainEvent' => false,
      'couponValue' => 0,
      'fee' => 50,
      'bundleDiscount' => 30,
      'start' => 'thursday 6pm',
      'end' => 'thursday 7:30pm',
      'category' => 'buffet',
      'requiresCoupon' => false,
      'couponAccessGroups' => ['Super Users', 'Administrator'],
      'isAddon' => true
    ], $quiet));

    // Friday
    $fri_breakfast = $this->AppendEvent(new ClawEvent((object)[
      'alias' => $prefix . '-fri-breakfast',
      'description' => 'Friday Breakfast Seminar',
      'badgeValue' => 'Fri',
      'eventPackageType' => EventPackageTypes::brunch_fri,
      'isMainEvent' => false,
      'couponValue' => 0,
      'fee' => 50,
      'bundleDiscount' => 5,
      'start' => 'friday noon',
      'end' => 'friday 1:30pm',
      'category' => 'buffet-breakfast',
      'requiresCoupon' => false,
      'couponAccessGroups' => ['Super Users', 'Administrator', 'CNMgmr'],
      'isAddon' => true
    ], $quiet));

    $bluf_buffet = $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'G',
      'alias' => $prefix . '-bluf-buffet',
      'description' => 'BLUF Dinner Buffet',
      'badgeValue' => 'BLUF',
      'eventPackageType' => EventPackageTypes::buffet_bluf,
      'isMainEvent' => false,
      'couponValue' => 110,
      'fee' => 110,
      'bundleDiscount' => 20,
      'start' => 'friday 7pm',
      'end' => 'friday 9pm',
      'category' => 'buffet',
      'requiresCoupon' => false,
      'couponAccessGroups' => ['Super Users', 'Administrator'],
      'isAddon' => true
    ], $quiet));

    $fri_buffet = $this->AppendEvent(new ClawEvent((object)[
      'alias' => $prefix . '-fri-buffet',
      'description' => 'Friday Fetish Buffet',
      'badgeValue' => 'Fri',
      'eventPackageType' => EventPackageTypes::buffet_fri,
      'isMainEvent' => false,
      'couponValue' => 0,
      'fee' => 90,
      'bundleDiscount' => 5,
      'start' => 'friday 6:30pm',
      'end' => 'friday 8pm',
      'category' => 'buffet',
      'requiresCoupon' => false,
      'couponAccessGroups' => [],
      'isAddon' => true
    ], $quiet));

    // Saturday
    $sat_breakfast = $this->AppendEvent(new ClawEvent((object)[
      'alias' => $prefix . '-sat-breakfast',
      'description' => 'Saturday Breakfast Seminar',
      'badgeValue' => 'Sat',
      'eventPackageType' => EventPackageTypes::brunch_sat,
      'isMainEvent' => false,
      'couponValue' => 0,
      'fee' => 50,
      'bundleDiscount' => 5,
      'start' => 'saturday noon',
      'end' => 'saturday 1:30pm',
      'category' => 'buffet-breakfast',
      'requiresCoupon' => false,
      'couponAccessGroups' => ['Super Users', 'Administrator', 'CNMgmr'],
      'isAddon' => true
    ], $quiet));

    $dinner = $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'D',
      'alias' => $prefix . '-dinner',
      'description' => 'International Leather Family Dinner',
      'eventPackageType' => EventPackageTypes::dinner,
      'isMainEvent' => false,
      'couponValue' => 120,
      'fee' => 120,
      'bundleDiscount' => 25,
      'start' => 'saturday 7pm',
      'end' => 'saturday 8:30pm',
      'category' => 'dinner',
      'requiresCoupon' => false,
      'couponAccessGroups' => ['Super Users', 'Administrator'],
      'isAddon' => true
    ], $quiet));

    // Sunday
    $sun_brunch = $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'B',
      'alias' => $prefix . '-brunch',
      'description' => 'Drag Brunch',
      'badgeValue' => 'Sun',
      'eventPackageType' => EventPackageTypes::brunch_sun,
      'isMainEvent' => false,
      'couponValue' => 70,
      'fee' => 70,
      'bundleDiscount' => 15,
      'start' => 'sunday noon',
      'end' => 'sunday 2pm',
      'category' => 'buffet-breakfast',
      'requiresCoupon' => false,
      'couponAccessGroups' => ['Super Users', 'Administrator'],
      'isAddon' => true
    ], $quiet));

    $sun_buffet = $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'H',
      'alias' => $prefix . '-sun-buffet',
      'description' => 'Sunday Supper Buffet',
      'badgeValue' => 'Sun',
      'eventPackageType' => EventPackageTypes::buffet_sun,
      'isMainEvent' => false,
      'couponValue' => 110,
      'fee' => 110,
      'bundleDiscount' => 20,
      'start' => 'sunday 6pm',
      'end' => 'sunday 7:30pm',
      'category' => 'buffet',
      'requiresCoupon' => false,
      'couponAccessGroups' => ['Super Users', 'Administrator'],
      'isAddon' => true
    ], $quiet));

    // Combos

    $vipMeal = $this->AppendEvent(new ClawEvent((object)[
      'alias' => $prefix . '-meals-combo-all-bluf',
      'description' => 'Meal Combo All',
      'eventPackageType' => EventPackageTypes::combo_meal_1,
      'isMainEvent' => false,
      'couponValue' => 0,
      'fee' => 540,
      'bundleDiscount' => 85,
      'category' => 'meal-combos',
      'requiresCoupon' => false,
      'couponAccessGroups' => [],
      'isAddon' => true,
      'meta' => [
        $wed_buffet,
        $thu_buffet,
        $fri_breakfast,
        $bluf_buffet,
        $sat_breakfast,
        $dinner,
        $sun_brunch,
        $sun_buffet
      ]
    ], $quiet));

    $this->AppendEvent(new ClawEvent((object)[
      'alias' => $prefix . '-meals-combo-all-fetish',
      'description' => 'Meal Combo All',
      'eventPackageType' => EventPackageTypes::combo_meal_2,
      'isMainEvent' => false,
      'couponValue' => 0,
      'fee' => 520,
      'bundleDiscount' => 70,
      'category' => 'meal-combos',
      'requiresCoupon' => false,
      'couponAccessGroups' => [],
      'isAddon' => true,
      'meta' => [
        $wed_buffet,
        $thu_buffet,
        $fri_breakfast,
        $fri_buffet,
        $sat_breakfast,
        $dinner,
        $sun_brunch,
        $sun_buffet
      ]
    ], $quiet));

    $this->AppendEvent(new ClawEvent((object)[
      'alias' => $prefix . '-meals-combo-dinners-bluf',
      'description' => 'Meal Combo Dinners',
      'eventPackageType' => EventPackageTypes::combo_meal_3,
      'isMainEvent' => false,
      'couponValue' => 0,
      'fee' => 390,
      'bundleDiscount' => 70,
      'category' => 'meal-combos',
      'requiresCoupon' => false,
      'couponAccessGroups' => [],
      'isAddon' => true,
      'meta' => [
        $thu_buffet,
        $bluf_buffet,
        $dinner,
        $sun_buffet
      ]
    ], $quiet));

    $this->AppendEvent(new ClawEvent((object)[
      'alias' => $prefix . '-meals-combo-dinners-fetish',
      'description' => 'Meal Combo Dinners',
      'eventPackageType' => EventPackageTypes::combo_meal_3,
      'isMainEvent' => false,
      'couponValue' => 0,
      'fee' => 370,
      'bundleDiscount' => 55,
      'category' => 'meal-combos',
      'requiresCoupon' => false,
      'couponAccessGroups' => [],
      'isAddon' => true,
      'meta' => [
        $thu_buffet,
        $fri_buffet,
        $dinner,
        $sun_buffet
      ]
    ], $quiet));

    #endregion

    #region Main Events (put after meals)
    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'A',
      'alias' => $prefix . '-attendee',
      'description' => 'Attendee',
      'eventPackageType' => EventPackageTypes::attendee,
      'isMainEvent' => true,
      'couponValue' => $base,
      'fee' => $base,
      'category' => 'attendee',
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => ['Super Users', 'Administrator', 'CNMgmr']
    ], $quiet));

    $this->AppendEvent(new ClawEvent((object)[
      'alias' => $prefix . '-vip',
      'description' => 'VIP',
      'eventPackageType' => EventPackageTypes::vip,
      'isMainEvent' => true,
      'couponValue' => 0,
      'fee' => 499,
      'category' => 'vip',
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => [],
      'meta' => [
        $vipMeal
      ]
    ], $quiet));

    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'C',
      'alias' => $prefix . '-staff-coordinator',
      'description' => 'Coordinator',
      'eventPackageType' => EventPackageTypes::claw_staff,
      'isVolunteer' => true,
      'isMainEvent' => true,
      'couponValue' => 100,
      'fee' => 100,
      'category' => 'staff-coordinator',
      'minShifts' => 0,
      'requiresCoupon' => true,
      'couponAccessGroups' => ['Super Users', 'Administrator'],
    ], $quiet));

    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'S',
      'alias' => $prefix . '-staff-onsite',
      'description' => 'Onsite Staff',
      'eventPackageType' => EventPackageTypes::event_staff,
      'isVolunteer' => true,
      'isMainEvent' => true,
      'couponValue' => 100,
      'fee' => 100,
      'category' => 'staff-onsite',
      'minShifts' => 0,
      'requiresCoupon' => true,
      'couponAccessGroups' => ['Super Users', 'Administrator']
    ], $quiet));

    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'T',
      'alias' => $prefix . '-staff-recruited',
      'description' => 'Recruited Volunteer',
      'eventPackageType' => EventPackageTypes::event_talent,
      'isVolunteer' => true,
      'isMainEvent' => true,
      'couponValue' => 100,
      'fee' => 100,
      'category' => 'staff-recruited',
      'minShifts' => 0,
      'requiresCoupon' => true,
      'couponAccessGroups' => ['Super Users', 'Administrator']
    ], $quiet));

    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'V',
      'alias' => $prefix . '-volunteer-2',
      'description' => 'Volunteer 2 Shifts',
      'eventPackageType' => EventPackageTypes::volunteer2,
      'isVolunteer' => true,
      'isMainEvent' => true,
      'couponValue' => 0,
      'fee' => 89,
      'category' => 'volunteer',
      'minShifts' => 2,
      'requiresCoupon' => false,
      'couponAccessGroups' => ['Super Users', 'Administrator', 'VolunteerMgmr'],
      'authNetProfile' => true
    ], $quiet));

    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'W',
      'alias' => $prefix . '-volunteer-3',
      'description' => 'Volunteer 3 Shifts',
      'eventPackageType' => EventPackageTypes::volunteer3,
      'isVolunteer' => true,
      'isMainEvent' => true,
      'couponValue' => 0,
      'fee' => 1,
      'category' => 'volunteer',
      'minShifts' => 3,
      'requiresCoupon' => false,
      'couponAccessGroups' => [],
      'authNetProfile' => true,
    ], $quiet));

    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'Y',
      'alias' => $prefix . '-volunteer-super',
      'description' => 'Super Volunteer',
      'eventPackageType' => EventPackageTypes::volunteersuper,
      'isVolunteer' => true,
      'isMainEvent' => true,
      'couponValue' => 1,
      'fee' => 1,
      'category' => 'volunteer',
      'minShifts' => 6,
      'requiresCoupon' => true,
      'couponAccessGroups' => ['Super Users', 'Administrator', 'VolunteerMgmr'],
      'authNetProfile' => true,
    ], $quiet));

    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'R',
      'alias' => $prefix . '-vendorcrew',
      'description' => 'Vendor Crew',
      'eventPackageType' => EventPackageTypes::vendor_crew,
      'isMainEvent' => true,
      'couponValue' => $base,
      'fee' => $base,
      'category' => 'vendorcrew',
      'minShifts' => 0,
      'requiresCoupon' => true,
      'couponAccessGroups' => ['Super Users', 'Administrator']
    ], $quiet));

    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'E',
      'alias' => $prefix . '-educator',
      'description' => 'Educator',
      'eventPackageType' => EventPackageTypes::educator,
      'isVolunteer' => true,
      'isMainEvent' => true,
      'couponValue' => 100,
      'fee' => 100,
      'category' => 'educator',
      'minShifts' => 0,
      'requiresCoupon' => true,
      'couponAccessGroups' => ['Super Users', 'Administrator', 'SkillsMgmr']
    ], $quiet));

    $this->AppendEvent(new ClawEvent((object)[
      'alias' => $prefix . '-attendee',
      'couponKey' => 'N',
      'description' => 'CLAW Nation',
      'eventPackageType' => EventPackageTypes::attendee,
      'isMainEvent' => false,
      'couponValue' => $base,
      'couponOnly' => true,
      'fee' => $base,
      'category' => 'attendee',
      'requiresCoupon' => false,
      'couponAccessGroups' => ['Super Users', 'Administrator', 'CNMgmr']
    ], $quiet));

    #endregion

    #region Day Passes
    $this->AppendEvent(new ClawEvent((object)[
      'alias' => $prefix . '-daypass-fri',
      'description' => 'Friday Day Pass',
      'eventPackageType' => EventPackageTypes::day_pass_fri,
      'isMainEvent' => true,
      'couponValue' => 0,
      'fee' => 150,
      'start' => 'friday 9am',
      'end' => 'saturday 2am',
      'category' => 'day-passes',
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => []
    ], $quiet));

    $this->AppendEvent(new ClawEvent((object)[
      'alias' => $prefix . '-daypass-sat',
      'description' => 'Saturday Day Pass',
      'eventPackageType' => EventPackageTypes::day_pass_sat,
      'isMainEvent' => true,
      'couponValue' => 0,
      'fee' => 150,
      'start' => 'saturday 9am',
      'end' => 'sunday 2am',
      'category' => 'day-passes',
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => []
    ], $quiet));

    $this->AppendEvent(new ClawEvent((object)[
      'alias' => $prefix . '-daypass-sun',
      'description' => 'Sunday Day Pass',
      'eventPackageType' => EventPackageTypes::day_pass_sun,
      'isMainEvent' => true,
      'couponValue' => 0,
      'fee' => 80,
      'start' => 'sunday 9am',
      'end' => 'next monday 2am',
      'category' => 'day-passes',
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => []
    ], $quiet));
    #endregion
  }

  public function Configs(): stdClass
  {
    $configs = (object)[];

    $events = (object)[
      'Fisting' => (object)[
        'description' => 'Fill That Hole',
        'types' => ['Fister', 'Fistee'],
        'date' => 'Thursday 5PM',
      ],
      'Ass Play' => (object)[
        'description' => 'Let\'s Get Cheeky',
        'types' => ['Top', 'Bottom'],
        'date' => 'Friday 6PM',
      ],
      'BDSM 1' => (object)[
        'description' => 'What\'s Your Kink?',
        'types' => ['Top', 'Bottom'],
        'date' => 'Thursday 5PM'
      ],
      'BDSM 2' => (object)[
        'description' => 'What\'s Your Kink?',
        'types' => ['Top', 'Bottom'],
        'date' => 'Friday 7PM'
      ],
      'Pets & Handlers 1' => (object)[
        'description' => 'Wags & Paws',
        'types' => ['Pet', 'Handler'],
        'date' => 'Thursday 7PM'
      ],
      'Smokers' => (object)[
        'description' => 'Hot Ash',
        'types' => ['Top', 'Bottom'],
        'date' => 'Thursday 8PM'
      ],
      'ABDL' => (object)[
        'description' => 'Welcome to My Crib',
        'types' => ['Big', 'little'],
        'date' => 'Friday 8PM',
      ],
      'Daddy & Boy' => (object)[
        'description' => 'Nurture is Nature',
        'types' => ['Daddy', 'boy'],
        'date' => 'Thursday 7PM',
      ],
      'Impact Play' => (object)[
        'description' => 'Meet & Beat',
        'types' => ['Top', 'Bottom'],
        'date' => 'Friday 6PM',
      ],
      'Rope Bondage' => (object)[
        'description' => 'Bound to Have Fun',
        'types' => ['Rigger', 'Bunny'],
        'date' => 'Thursday 7PM',
      ],
      'Uniforms' => (object)[
        'description' => 'Ready For Inspection',
        'types' => ['Top', 'Bottom'],
        'date' => 'Thursday 6PM',
      ],
      'Rubber' => (object)[
        'description' => 'Shiny or Matte?',
        'types' => ['Top', 'Bottom'],
        'date' => 'Friday 8PM',
      ],
      'Pets & Handlers 2' => (object)[
        'description' => 'Wags & Paws',
        'types' => ['Pet', 'Handler'],
        'date' => 'Friday 7PM',
      ],
    ];

    $configs->speeddating = $events;

    $events = (object)[
      'Equipment Delivery' => (object)[
        'description' => 'Please note: Renaissance guests only. You are still responsible for returning equipment.',
        'price' => 50,
        'pricetext' => '',
        'capacity' => 0,
      ],
      'Rim Seat Rental' => (object)[
        'description' => '',
        'price' => 125,
        'pricetext' => '$50 + $75 deposit',
        'capacity' => 10,
      ],
      'Sling Kit Rental' => (object)[
        'description' => 'Rental Includes:Portable sling frame, deluxe canvas sling, chain, 2 stirrups, carrying bag, cleaning kit, 10 disposable liners.',
        'price' => 250,
        'pricetext' => '$100 + $150 deposit',
        'capacity' => 20,
      ],
    ];

    $configs->rentals = $events;

    $advertisingCategoryId = 'sponsorships-advertising';
    $logoPlacementCategoryId = 'sponsorships-logo';
    $masterSustainingCategoryId = 'sponsorships-master-sustaining';
    $blackCategoryId = 'sponsorships-black';
    $blueCategoryId = 'sponsorships-blue';
    $goldCategoryId = 'sponsorships-gold';
    $heartCategoryId = 'donations-leather-heart';

    $leatherHeartDescription = <<< HTML
Help someone in the community who is facing financial hardship attend CLAW 24. 
<a href="index.php?option=com_content&view=article&id=256&catid=77">Leather Heart and Volunteer Bridge Donations Details</a>
HTML;

    $leatherBridgeDescription = <<< HTML
Buys tickets to all banquets at CLAW 24 for volunteers who want to be part of those 
community networks but cannot afford the meal prices. 
<a href="index.php?option=com_content&view=article&id=256&catid=77">Leather Heart and Volunteer Bridge Donations Details</a>
HTML;

    $events = [
      (object)[
        "main_category_id" => $heartCategoryId,
        "event_capacity" => 0,
        "individual_price" => 250,
        "title" => "Leather Heart",
        "description" => $leatherHeartDescription
      ],
      (object)[
        "main_category_id" => $heartCategoryId,
        "event_capacity" => 0,
        "individual_price" => 250,
        "title" => "Leather Heart",
        "description" => $leatherBridgeDescription
      ],
      (object)[
        "main_category_id" => $advertisingCategoryId,
        "event_capacity" => 0,
        "individual_price" => 250,
        "title" => "Full Page Ad",
        "description" => "Full page ad (8\"H x 5\"W) in CLAW 24 Yearbook. Registration email contains full specifications."
      ],
      (object)[
        "main_category_id" => $advertisingCategoryId,
        "event_capacity" => 1,
        "individual_price" => 1200,
        "title" => "Outside Rear Cover (Color)",
        "description" => "Same dimensions as Full Page Ad"
      ],
      (object)[
        "main_category_id" => $advertisingCategoryId,
        "event_capacity" => 1,
        "individual_price" => 550,
        "title" => "Inside Front Cover (Color)",
        "description" => "Same dimensions as Full Page Ad"
      ],
      (object)[
        "main_category_id" => $advertisingCategoryId,
        "event_capacity" => 1,
        "individual_price" => 550,
        "title" => "Page 3 (Color)",
        "description" => "Same dimensions as Full Page Ad"
      ],
      (object)[
        "main_category_id" => $advertisingCategoryId,
        "event_capacity" => 1,
        "individual_price" => 450,
        "title" => "Inside Back Cover (Color)",
        "description" => "Same dimensions as Full Page Ad"
      ],
      (object)[
        "main_category_id" => $advertisingCategoryId,
        "event_capacity" => 2,
        "individual_price" => 400,
        "title" => "Page 5 or 7 (Color)",
        "description" => "Same dimensions as Full Page Ad"
      ],
      (object)[
        "main_category_id" => $advertisingCategoryId,
        "event_capacity" => 2,
        "individual_price" => 350,
        "title" => "Page 4 or 6 (Color)",
        "description" => "Same dimensions as Full Page Ad"
      ],
      (object)[
        "main_category_id" => $advertisingCategoryId,
        "event_capacity" => 1,
        "individual_price" => 350,
        "title" => "Page Opposite Inside Back Cover",
        "description" => "Same dimensions as Full Page Ad"
      ],
      (object)[
        "main_category_id" => $advertisingCategoryId,
        "event_capacity" => 1,
        "individual_price" => 750,
        "title" => "Two-Page Centerfold",
        "description" => "Requires 2 Full Page Ad graphics"
      ],
      (object)[
        "main_category_id" => $advertisingCategoryId,
        "event_capacity" => 0,
        "individual_price" => 500,
        "title" => "Two-Page Spread",
        "description" => "Requires 2 Full Page Ad graphics"
      ],
      (object)[
        "main_category_id" => $advertisingCategoryId,
        "event_capacity" => 0,
        "individual_price" => 200,
        "title" => "Additional Pages",
        "description" => "Full page ad (8\"H x 5\"W) in CLAW Yearbook."
      ],
      (object)[
        "main_category_id" => $advertisingCategoryId,
        "event_capacity" => 0,
        "individual_price" => 150,
        "title" => "Half Page Ad",
        "description" => "Half Page: 3 7/8;\" H x 5\" W"
      ],
      (object)[
        "main_category_id" => $advertisingCategoryId,
        "event_capacity" => 0,
        "individual_price" => 100,
        "title" => "Quarter Page Ad",
        "description" => "Quarter Page: 3 7/8;\" H x 2 3/8;\" W"
      ],
      (object)[
        "main_category_id" => $logoPlacementCategoryId,
        "event_capacity" => 0,
        "individual_price" => 100,
        "title" => "Run Bag Inserts (Commercial)",
        "description" => "2,500 pieces (to be received by April 1, 2023)"
      ],
      (object)[
        "main_category_id" => $logoPlacementCategoryId,
        "event_capacity" => 0,
        "individual_price" => 40,
        "title" => "Run Bag Inserts (Non-Profit)",
        "description" => "2,500 pieces (to be received by April 1, 2023)"
      ],
      (object)[
        "main_category_id" => $logoPlacementCategoryId,
        "event_capacity" => 0,
        "individual_price" => 300,
        "title" => "Website Banner (6 months)",
        "description" => "Your banner ad on the clawinfo.org homepage for 6 months<br /><strong>Image specifications:</strong> 940x200, jpg"
      ],
      (object)[
        "main_category_id" => $logoPlacementCategoryId,
        "event_capacity" => 0,
        "individual_price" => 500,
        "title" => "Website Banner (12 months)",
        "description" => "Your banner ad on the clawinfo.org homepage for 12 months<br /><strong>Image specifications:</strong> 940x200, jpg"
      ],
      (object)[
        "main_category_id" => $logoPlacementCategoryId,
        "event_capacity" => 0,
        "individual_price" => 600,
        "title" => "CLAW Mobile App (YAPP)",
        "description" => "Sponsor the official CLAW mobile app. Over 900 downloads and 10,000 views a day during the event.<br /><strong>Image specifications:</strong> 600x200, jpg"
      ],
      (object)[
        "main_category_id" => $logoPlacementCategoryId,
        "event_capacity" => 0,
        "individual_price" => 500,
        "title" => "Three e-blast Banner Ads",
        "description" => "Your banner ad on three CLAW/Getaway eblasts (sent to more than 10,000 great addresses)<br /> <strong>Image specifications:</strong> 600x125, jpg"
      ],
      (object)[
        "main_category_id" => $logoPlacementCategoryId,
        "event_capacity" => 0,
        "individual_price" => 400,
        "title" => "Ad Combo 1",
        "description" => "Save $250 with this Bundle! Includes Yearbook full page ad, run bag inserts, and website banner ad (6 months)<p><small>2,500 run bag inserts (to be received by April 1)</small></p>"
      ],
      (object)[
        "main_category_id" => $logoPlacementCategoryId,
        "event_capacity" => 0,
        "individual_price" => 750,
        "title" => "Ad Combo 2",
        "description" => "Save $400 with This Bundle! Includes Yearbook full page ad, run bag inserts, and website banner ad (6 months), and Banner ads in three e-blasts.<p><small>2,500 run bag insert (to be received by April 1)</small></p>"
      ],
      (object)[
        "main_category_id" => $logoPlacementCategoryId,
        "event_capacity" => 0,
        "individual_price" => 1200,
        "title" => "Ad Combo 3",
        "description" => "Save $1000 with this Bundle: Includes full page ad in both Yearbooks (L.A. Leather Getaway and CLAW 24), run bag inserts at both events, website banner ad (12 months), and Banner ads in six e-blasts.<p><small>2,500 run bag inserts (to be received by April 1 for CLAW 24)</small></p>"
      ],
      (object)[
        "main_category_id" => $masterSustainingCategoryId,
        "event_capacity" => 0,
        "individual_price" => 5000,
        "title" => "Master Sponsorship One Event",
        "description" => "Please see the sponsorship page for details."
      ],
      (object)[
        "main_category_id" => $masterSustainingCategoryId,
        "event_capacity" => 0,
        "individual_price" => 1500,
        "title" => "Sustaining Sponsorship One Event",
        "description" => "Please see the sponsorship page for details."
      ],
      (object)[
        "main_category_id" => $masterSustainingCategoryId,
        "event_capacity" => 0,
        "individual_price" => 7000,
        "title" => "Master Sponsorship Year Round",
        "description" => "Please see the sponsorship page for details."
      ],
      (object)[
        "main_category_id" => $masterSustainingCategoryId,
        "event_capacity" => 0,
        "individual_price" => 2200,
        "title" => "Sustaining Sponsorship Year Round",
        "description" => "Please see the sponsorship page for details."
      ],
      (object)[
        "main_category_id" => $blackCategoryId,
        "event_capacity" => 0,
        "individual_price" => 2000,
        "title" => "Black-Level Sponsorship",
        "description" => "Partner Event Sponsorship - We Work with You to Create <i>CLAW 24</i>"
      ],
      (object)[
        "main_category_id" => $blueCategoryId,
        "event_capacity" => 0,
        "individual_price" => 400,
        "title" => "Blue-Level Sponsorship",
        "description" => "Partner Event Sponsorship - We Work with You to Create <i>CLAW 24</i>"
      ],
      (object)[
        "main_category_id" => $goldCategoryId,
        "event_capacity" => 0,
        "individual_price" => 800,
        "title" => "Gold-Level Sponsorship",
        "description" => "Partner Event Sponsorship - We Work with You to Create <i>CLAW 24</i>"
      ]
    ];

    $configs->sponsorships = $events;

    return $configs;
  }
}
