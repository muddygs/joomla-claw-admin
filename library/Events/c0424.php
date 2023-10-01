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
    $base = 239;

    #region Main Events
    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'A',
      'alias' => $prefix . '-attendee',
      'link' => $prefix . '-reg-att',
      'description' => 'Attendee',
      'clawPackageType' => EventPackageTypes::attendee,
      'isMainEvent' => true,
      'couponValue' => $base,
      'fee' => $base,
      'eventId' => ClawEvents::getEventIdByAlias($prefix . '-attendee', $quiet),
      'category' => ClawEvents::getCategoryId('attendee'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => ['Super Users', 'Administrator', 'CNMgmr']
    ]));

    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => '',
      'alias' => $prefix . '-vip',
      'link' => $prefix . '-reg-vip',
      'description' => 'VIP',
      'clawPackageType' => EventPackageTypes::vip,
      'isMainEvent' => true,
      'couponValue' => 0,
      'fee' => 750,
      'eventId' => ClawEvents::getEventIdByAlias($prefix . '-vip', $quiet),
      'category' => ClawEvents::getCategoryId('vip'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => []
    ]));

    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'C',
      'alias' => $prefix . '-staff-coordinator',
      'link' => $prefix . '-reg-claw',
      'description' => 'Coordinator',
      'clawPackageType' => EventPackageTypes::claw_staff,
      'isVolunteer' => true,
      'isMainEvent' => true,
      'couponValue' => 100,
      'fee' => 100,
      'eventId' => ClawEvents::getEventIdByAlias($prefix . '-staff-coordinator', $quiet),
      'category' => ClawEvents::getCategoryId('staff-coordinator'),
      'minShifts' => 0,
      'requiresCoupon' => true,
      'couponAccessGroups' => ['Super Users', 'Administrator'],
    ]));

    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'S',
      'alias' => $prefix . '-staff-onsite',
      'link' => $prefix . '-reg-sta',
      'description' => 'Onsite Staff',
      'clawPackageType' => EventPackageTypes::event_staff,
      'isVolunteer' => true,
      'isMainEvent' => true,
      'couponValue' => 100,
      'fee' => 100,
      'eventId' => ClawEvents::getEventIdByAlias($prefix . '-staff-onsite', $quiet),
      'category' => ClawEvents::getCategoryId('staff-onsite'),
      'minShifts' => 0,
      'requiresCoupon' => true,
      'couponAccessGroups' => ['Super Users', 'Administrator']
    ]));

    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'T',
      'alias' => $prefix . '-staff-recruited',
      'link' => $prefix . '-reg-tal',
      'description' => 'Recruited Volunteer',
      'clawPackageType' => EventPackageTypes::event_talent,
      'isVolunteer' => true,
      'isMainEvent' => true,
      'couponValue' => 100,
      'fee' => 100,
      'eventId' => ClawEvents::getEventIdByAlias($prefix . '-staff-recruited', $quiet),
      'category' => ClawEvents::getCategoryId('staff-recruited'),
      'minShifts' => 0,
      'requiresCoupon' => true,
      'couponAccessGroups' => ['Super Users', 'Administrator']
    ]));

    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'V',
      'alias' => $prefix . '-volunteer-2',
      'link' => $prefix . '-reg-vol2',
      'description' => 'Volunteer 2 Shifts',
      'clawPackageType' => EventPackageTypes::volunteer2,
      'isVolunteer' => true,
      'isMainEvent' => true,
      'couponValue' => 0,
      'fee' => 99,
      'eventId' => ClawEvents::getEventIdByAlias($prefix . '-volunteer-2', $quiet),
      'category' => ClawEvents::getCategoryId('volunteer'),
      'minShifts' => 2,
      'requiresCoupon' => false,
      'couponAccessGroups' => ['Super Users', 'Administrator', 'VolunteerMgmr'],
      'authNetProfile' => true
    ]));

    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'W',
      'alias' => $prefix . '-volunteer-3',
      'link' => $prefix . '-reg-vol3',
      'description' => 'Volunteer 3 Shifts',
      'clawPackageType' => EventPackageTypes::volunteer3,
      'isVolunteer' => true,
      'isMainEvent' => true,
      'couponValue' => 0,
      'fee' => 1,
      'eventId' => ClawEvents::getEventIdByAlias($prefix . '-volunteer-3', $quiet),
      'category' => ClawEvents::getCategoryId('volunteer'),
      'minShifts' => 3,
      'requiresCoupon' => false,
      'couponAccessGroups' => [],
      'authNetProfile' => true,
    ]));

    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'Y',
      'alias' => $prefix . '-volunteer-super',
      'link' => $prefix . '-reg-super',
      'description' => 'Super Volunteer',
      'clawPackageType' => EventPackageTypes::volunteersuper,
      'isVolunteer' => true,
      'isMainEvent' => true,
      'couponValue' => 1,
      'fee' => 1,
      'eventId' => ClawEvents::getEventIdByAlias($prefix . '-volunteer-super', $quiet),
      'category' => ClawEvents::getCategoryId('volunteer'),
      'minShifts' => 6,
      'requiresCoupon' => true,
      'couponAccessGroups' => ['Super Users', 'Administrator', 'VolunteerMgmr'],
      'authNetProfile' => true,
    ]));

    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'R',
      'alias' => $prefix . '-vendorcrew',
      'link' => $prefix . '-reg-ven',
      'description' => 'Vendor Crew',
      'clawPackageType' => EventPackageTypes::vendor_crew,
      'isMainEvent' => true,
      'couponValue' => $base,
      'fee' => $base,
      'eventId' => ClawEvents::getEventIdByAlias($prefix . '-vendorcrew', $quiet),
      'category' => ClawEvents::getCategoryId('vendorcrew'),
      'minShifts' => 0,
      'requiresCoupon' => true,
      'couponAccessGroups' => ['Super Users', 'Administrator']
    ]));

    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'E',
      'alias' => $prefix . '-educator',
      'link' => $prefix . '-reg-edu',
      'description' => 'Educator',
      'clawPackageType' => EventPackageTypes::educator,
      'isVolunteer' => true,
      'isMainEvent' => true,
      'couponValue' => 100,
      'fee' => 100,
      'eventId' => ClawEvents::getEventIdByAlias($prefix . '-educator', $quiet),
      'category' => ClawEvents::getCategoryId('educator'),
      'minShifts' => 0,
      'requiresCoupon' => true,
      'couponAccessGroups' => ['Super Users', 'Administrator', 'SkillsMgmr']
    ]));

    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'N',
      'link' => $prefix . '-reg-att',
      'description' => 'CLAW Nation',
      'clawPackageType' => EventPackageTypes::attendee,
      'isMainEvent' => false,
      'couponValue' => $base,
      'fee' => $base,
      'eventId' => ClawEvents::getEventIdByAlias($prefix . '-attendee', $quiet),
      'category' => ClawEvents::getCategoryId('attendee'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => ['Super Users', 'Administrator', 'CNMgmr']
    ]));

    #endregion

    #region Meals
    // Addons (for coupon generation)
    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'D',
      'alias' => $prefix . '-dinner',
      'description' => 'International Leather Family Dinner',
      'clawPackageType' => EventPackageTypes::dinner,
      'isMainEvent' => false,
      'couponValue' => 95,
      'fee' => 95,
      'bundleDiscount' => 20,
      'start' => 'saturday 7pm',
      'end' => 'saturday 9pm',
      'eventId' => ClawEvents::getEventIdByAlias($prefix . '-dinner', $quiet),
      'category' => ClawEvents::getCategoryId('dinner'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => ['Super Users', 'Administrator'],
      'isAddon' => true
    ]));

    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'B',
      'alias' => $prefix . '-brunch',
      'description' => 'Sunday Brunch',
      'clawPackageType' => EventPackageTypes::brunch_sun,
      'isMainEvent' => false,
      'couponValue' => 65,
      'fee' => 65,
      'bundleDiscount' => 15,
      'start' => 'sunday noon',
      'end' => 'sunday 2pm',
      'eventId' => ClawEvents::getEventIdByAlias($prefix . '-brunch', $quiet),
      'category' => ClawEvents::getCategoryId('buffet-breakfast'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => ['Super Users', 'Administrator'],
      'isAddon' => true
    ]));

    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => '',
      'alias' => $prefix . '-fri-breakfast',
      'description' => 'Friday Breakfast Seminar',
      'clawPackageType' => EventPackageTypes::brunch_fri,
      'isMainEvent' => false,
      'couponValue' => 0,
      'fee' => 50,
      'bundleDiscount' => 10,
      'start' => 'friday 11am',
      'end' => 'friday 1pm',
      'eventId' => ClawEvents::getEventIdByAlias($prefix . '-fri-breakfast', $quiet),
      'category' => ClawEvents::getCategoryId('buffet-breakfast'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => ['Super Users', 'Administrator', 'CNMgmr'],
      'isAddon' => true
    ]));

    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => '',
      'alias' => $prefix . '-sat-breakfast',
      'description' => 'Saturday Breakfast Seminar',
      'clawPackageType' => EventPackageTypes::brunch_sat,
      'isMainEvent' => false,
      'couponValue' => 0,
      'fee' => 50,
      'bundleDiscount' => 10,
      'start' => 'saturday 11am',
      'end' => 'saturday 1pm',
      'eventId' => ClawEvents::getEventIdByAlias($prefix . '-sat-breakfast', $quiet),
      'category' => ClawEvents::getCategoryId('buffet-breakfast'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => ['Super Users', 'Administrator', 'CNMgmr'],
      'isAddon' => true
    ]));

    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'G',
      'alias' => $prefix . '-fri-buffet',
      'description' => 'BLUF Dinner Buffet',
      'clawPackageType' => EventPackageTypes::buffet_fri,
      'isMainEvent' => false,
      'couponValue' => 90,
      'fee' => 90,
      'bundleDiscount' => 15,
      'start' => 'friday 7pm',
      'end' => 'friday 9pm',
      'eventId' => ClawEvents::getEventIdByAlias($prefix . '-fri-buffet', $quiet),
      'category' => ClawEvents::getCategoryId('buffet'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => ['Super Users', 'Administrator'],
      'isAddon' => true
    ]));

    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'H',
      'alias' => $prefix . '-sun-buffet',
      'description' => 'Sunday Supper Buffet',
      'clawPackageType' => EventPackageTypes::buffet_sun,
      'isMainEvent' => false,
      'couponValue' => 90,
      'fee' => 90,
      'bundleDiscount' => 15,
      'start' => 'sunday 7pm',
      'end' => 'sunday 9pm',
      'eventId' => ClawEvents::getEventIdByAlias($prefix . '-sun-buffet', $quiet),
      'category' => ClawEvents::getCategoryId('buffet'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => ['Super Users', 'Administrator'],
      'isAddon' => true
    ]));

    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => '',
      'alias' => $prefix . '-meals-combo-all',
      'description' => 'Meal Combo All',
      'clawPackageType' => EventPackageTypes::meal_combo_all,
      'isMainEvent' => false,
      'couponValue' => 0,
      'fee' => 500,
      'bundleDiscount' => 50,
      'eventId' => ClawEvents::getEventIdByAlias($prefix . '-meals-combo-all', $quiet),
      'category' => ClawEvents::getCategoryId('meal-combos'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => [],
      'isAddon' => true
    ]));

    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => '',
      'alias' => $prefix . '-meals-combo-dinners',
      'description' => 'Meal Combo Dinners',
      'clawPackageType' => EventPackageTypes::meal_combo_dinners,
      'isMainEvent' => false,
      'couponValue' => 0,
      'fee' => 300,
      'bundleDiscount' => 30,
      'eventId' => ClawEvents::getEventIdByAlias($prefix . '-meals-combo-dinners', $quiet),
      'category' => ClawEvents::getCategoryId('meal-combos'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => [],
      'isAddon' => true
    ]));

    #endregion

    #region Day Passes
    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'FRI',
      'alias' => $prefix . '-daypass-fri',
      'link' => 'onsite',
      'description' => 'Friday Day Pass',
      'clawPackageType' => EventPackageTypes::day_pass_fri,
      'isMainEvent' => true,
      'couponValue' => 0,
      'fee' => 150,
      'start' => 'friday 9am',
      'end' => 'saturday 2am',
      'eventId' => ClawEvents::getEventIdByAlias($prefix . '-daypass-fri', $quiet),
      'category' => ClawEvents::getCategoryId('day-passes'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => []
    ]));

    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'SAT',
      'alias' => $prefix . '-daypass-sat',
      'link' => 'onsite',
      'description' => 'Saturday Day Pass',
      'clawPackageType' => EventPackageTypes::day_pass_sat,
      'isMainEvent' => true,
      'couponValue' => 0,
      'fee' => 150,
      'start' => 'saturday 9am',
      'end' => 'sunday 2am',
      'eventId' => ClawEvents::getEventIdByAlias($prefix . '-daypass-sat', $quiet),
      'category' => ClawEvents::getCategoryId('day-passes'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => []
    ]));

    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'SUN',
      'alias' => $prefix . '-daypass-sun',
      'link' => 'onsite',
      'description' => 'Sunday Day Pass',
      'clawPackageType' => EventPackageTypes::day_pass_sun,
      'isMainEvent' => true,
      'couponValue' => 0,
      'fee' => 80,
      'start' => 'sunday 9am',
      'end' => 'next monday 2am',
      'eventId' => ClawEvents::getEventIdByAlias($prefix . '-daypass-sun', $quiet),
      'category' => ClawEvents::getCategoryId('day-passes'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => []
    ]));
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
      // 'Heavy Steel Cage Rental' => (object)[
      //     'description' => '',
      //     'price' => 200,
      //     'pricetext' => '',
      //     'capacity' => 3,
      // ],
      'Rim Seat Rental' => (object)[
        'description' => '',
        'price' => 125,
        'pricetext' => '$50 + $75 deposit',
        'capacity' => 10,
      ],
      // 'Handled T-Leg Rim Seat Rental' => (object)[
      //     'description' => '',
      //     'price' => 175,
      //     'pricetext' => '$75 + $100 deposit',
      //     'capacity' => 0,
      // ],
      'Sling Kit Rental' => (object)[
        'description' => 'Rental Includes:Portable sling frame, deluxe canvas sling, chain, 2 stirrups, carrying bag, cleaning kit, 10 disposable liners.',
        'price' => 250,
        'pricetext' => '$100 + $150 deposit',
        'capacity' => 20,
      ],
    ];

    $configs->rentals = $events;

    $advertisingCategoryId = ClawEvents::getCategoryId('sponsorships-advertising');
    $logoPlacementCategoryId = ClawEvents::getCategoryId('sponsorships-logo');
    $masterSustainingCategoryId = ClawEvents::getCategoryId('sponsorships-master-sustaining');
    $blackCategoryId = ClawEvents::getCategoryId('sponsorships-black');
    $blueCategoryId = ClawEvents::getCategoryId('sponsorships-blue');
    $goldCategoryId = ClawEvents::getCategoryId('sponsorships-gold');

    $events = [
      (object)[
        "main_category_id" => $advertisingCategoryId,
        "event_capacity" => 0,
        "individual_price" => 250,
        "title" => "Full Page Ad",
        "description" => "Full page ad (8\"H x 5\"W) in CLAW 23 Yearbook. Registration email contains full specifications."
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
        "description" => "Save $1000 with this Bundle: Includes full page ad in both Yearbooks (L.A. Leather Getaway and CLAW 23), run bag inserts at both events, website banner ad (12 months), and Banner ads in six e-blasts.<p><small>2,500 run bag inserts (to be received by April 1 for CLAW 23)</small></p>"
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
        "description" => "Partner Event Sponsorship - We Work with You to Create <i>CLAW 23</i>"
      ],
      (object)[
        "main_category_id" => $blueCategoryId,
        "event_capacity" => 0,
        "individual_price" => 400,
        "title" => "Blue-Level Sponsorship",
        "description" => "Partner Event Sponsorship - We Work with You to Create <i>CLAW 23</i>"
      ],
      (object)[
        "main_category_id" => $goldCategoryId,
        "event_capacity" => 0,
        "individual_price" => 800,
        "title" => "Gold-Level Sponsorship",
        "description" => "Partner Event Sponsorship - We Work with You to Create <i>CLAW 23</i>"
      ]
    ];

    $configs->sponsorships = $events;

    return $configs;
  }
}
