<?php
namespace ClawCorpLib\Lib;

defined('_JEXEC') or die('Restricted access');

use ClawCorpLib\Lib\ClawEvent;
use ClawCorpLib\Lib\ClawEventTypes AS clawEventType;
use ClawCorpLib\Lib\PackageTypes AS clawPackageType;

$info->description = 'CLAW 23';
$info->location = 'Cleveland, OH';
$info->locationAlias = 'renaissance-cleveland';
$info->start_date = '2023-04-03 00:00:00'; // Monday
$info->end_date = 'next week Tuesday'; // Calculated
$info->prefix = 'C23';
$info->shiftPrefix = strtolower($info->prefix.'-shift-cle-');
$info->mainAllowed = true;
$info->cancelBy = '2023-04-01 00:00:00'; // Varies too much to calculate
$info->eventType = clawEventType::main;

$prefix = strtolower($info->prefix);

// Addons (for coupon generation)
$events[] = new clawEvent((object)[
  'couponKey' => 'D',
  'description' => 'Dinner',
  'clawPackageType' => clawPackageType::dinner,
  'isMainEvent' => false,
  'couponValue' => 95,
  'eventId' => self::$eventIds[$prefix.'-dinner']->id,
  'category' => self::$categoryIds['dinner']->id,
  'minShifts' => 0,
  'requiresCoupon' => false,
  'couponAccessGroups' => ['Super Users', 'Administrator'],
  'isAddon' => true
]);

$events[] = new clawEvent((object)[
  'couponKey' => 'B',
  'description' => 'Sun Brunch',
  'clawPackageType' => clawPackageType::brunch_sun,
  'isMainEvent' => false,
  'couponValue' => 65,
  'eventId' => self::$eventIds[$prefix.'-brunch']->id,
  'category' => self::$categoryIds['buffet-breakfast']->id,
  'minShifts' => 0,
  'requiresCoupon' => false,
  'couponAccessGroups' => ['Super Users', 'Administrator'],
  'isAddon' => true
]);

// $events[] = new clawEvent((object)[
//   'couponKey' => '',
//   'description' => 'Sat Seminar',
//   'clawPackageType' => clawPackageType::brunch_sat,
//   'isMainEvent' => false,
//   'couponValue' => 0,
//   'eventId' => self::$eventIds[$prefix.'-sat-breakfast']->id,
//   'category' => self::$categoryIds['buffet-breakfast']->id,
//   'minShifts' => 0,
//   'requiresCoupon' => false,
//   'couponAccessGroups' => ['Super Users', 'Administrator', 'CNMgmr'],
//   'isAddon' => true
// ]);

// $events[] = new clawEvent((object)[
//   'couponKey' => '',
//   'description' => 'Fri Seminar',
//   'clawPackageType' => clawPackageType::brunch_fri,
//   'isMainEvent' => false,
//   'couponValue' => 0,
//   'eventId' => self::$eventIds[$prefix.'-fri-breakfast']->id,
//   'category' => self::$categoryIds['buffet-breakfast']->id,
//   'minShifts' => 0,
//   'requiresCoupon' => false,
//   'couponAccessGroups' => ['Super Users', 'Administrator', 'CNMgmr'],
//   'isAddon' => true
// ]);

// $events[] = new clawEvent((object)[
//   'couponKey' => '',
//   'description' => 'Wed Buffet',
//   'clawPackageType' => clawPackageType::buffet_thu,
//   'isMainEvent' => false,
//   'couponValue' => 0,
//   'eventId' => self::$eventIds[$prefix.'-wed-buffet']->id,
//   'category' => self::$categoryIds['buffet']->id,
//   'minShifts' => 0,
//   'requiresCoupon' => false,
//   'couponAccessGroups' => [],
//   'isAddon' => true
// ]);


// $events[] = new clawEvent((object)[
//   'couponKey' => 'F',
//   'description' => 'Thu Buffet',
//   'clawPackageType' => clawPackageType::buffet_thu,
//   'isMainEvent' => false,
//   'couponValue' => 80,
//   'eventId' => self::$eventIds[$prefix.'-thu-buffet']->id,
//   'category' => self::$categoryIds['buffet']->id,
//   'minShifts' => 0,
//   'requiresCoupon' => false,
//   'couponAccessGroups' => ['Super Users', 'Administrator'],
//   'isAddon' => true
// ]);

$events[] = new clawEvent((object)[
  'couponKey' => 'G',
  'description' => 'Fri Buffet',
  'clawPackageType' => clawPackageType::buffet_fri,
  'isMainEvent' => false,
  'couponValue' => 90,
  'eventId' => self::$eventIds[$prefix.'-fri-buffet']->id,
  'category' => self::$categoryIds['buffet']->id,
  'minShifts' => 0,
  'requiresCoupon' => false,
  'couponAccessGroups' => ['Super Users', 'Administrator'],
  'isAddon' => true
]);

// $events[] = new clawEvent((object)[
//   'couponKey' => 'H',
//   'description' => 'Sun Buffet',
//   'clawPackageType' => clawPackageType::buffet_sun,
//   'isMainEvent' => false,
//   'couponValue' => 90,
//   'eventId' => self::$eventIds[$prefix.'-sun-buffet']->id,
//   'category' => self::$categoryIds['buffet']->id,
//   'minShifts' => 0,
//   'requiresCoupon' => false,
//   'couponAccessGroups' => ['Super Users', 'Administrator'],
//   'isAddon' => true
// ]);

// $events[] = new clawEvent((object)[
//   'couponKey' => '',
//   'description' => 'Meal Combo All',
//   'clawPackageType' => clawPackageType::meal_combo_all,
//   'isMainEvent' => false,
//   'couponValue' => 0,
//   'eventId' => self::$eventIds[$prefix.'-meals-combo-all']->id,
//   'category' => self::$categoryIds['meal-combos']->id,
//   'minShifts' => 0,
//   'requiresCoupon' => false,
//   'couponAccessGroups' => [],
//   'isAddon' => true
// ]);

// $events[] = new clawEvent((object)[
//   'couponKey' => '',
//   'description' => 'Meal Combo Dinners',
//   'clawPackageType' => clawPackageType::meal_combo_dinners,
//   'isMainEvent' => false,
//   'couponValue' => 0,
//   'eventId' => self::$eventIds[$prefix.'-meals-combo-dinners']->id,
//   'category' => self::$categoryIds['meal-combos']->id,
//   'minShifts' => 0,
//   'requiresCoupon' => false,
//   'couponAccessGroups' => [],
//   'isAddon' => true
// ]);

// Events (for coupon generation)
$events[] = new clawEvent((object)[
  'couponKey' => 'A',
  'link' => $info->prefix.'-reg-att',
  'description' => 'Attendee',
  'clawPackageType' => clawPackageType::attendee,
  'isMainEvent' => true,
  'couponValue' => 239,
  'eventId' => self::$eventIds[$prefix.'-attendee']->id,
  'category' => self::$categoryIds['attendee']->id,
  'minShifts' => 0,
  'requiresCoupon' => false,
  'couponAccessGroups' => ['Super Users', 'Administrator', 'CNMgmr']
]);

$events[] = new clawEvent((object)[
  'couponKey' => '',
  'link' => $info->prefix.'-reg-vip',
  'description' => 'VIP',
  'clawPackageType' => clawPackageType::vip,
  'isMainEvent' => true,
  'couponValue' => 0,
  'eventId' => self::$eventIds[$prefix.'-vip']->id,
  'category' => self::$categoryIds['vip']->id,
  'minShifts' => 0,
  'requiresCoupon' => false,
  'couponAccessGroups' => []
]);

$events[] = new clawEvent((object)[
  'couponKey' => 'C',
  'link' => $info->prefix.'-reg-claw',
  'description' => 'Coordinator',
  'clawPackageType' => clawPackageType::claw_staff,
  'isMainEvent' => true,
  'couponValue' => 100,
  'eventId' => self::$eventIds[$prefix.'-staff-coordinator']->id,
  'category' => self::$categoryIds['staff-coordinator']->id,
  'minShifts' => 0,
  'requiresCoupon' => true,
  'couponAccessGroups' => ['Super Users', 'Administrator']
]);

$events[] = new clawEvent((object)[
  'couponKey' => 'S',
  'link' => $info->prefix.'-reg-sta',
  'description' => 'Onsite Staff',
  'clawPackageType' => clawPackageType::event_staff,
  'isMainEvent' => true,
  'couponValue' => 100,
  'eventId' => self::$eventIds[$prefix.'-staff-onsite']->id,
  'category' => self::$categoryIds['staff-onsite']->id,
  'minShifts' => 0,
  'requiresCoupon' => true,
  'couponAccessGroups' => ['Super Users', 'Administrator']
]);

$events[] = new clawEvent((object)[
  'couponKey' => 'T',
  'link' => $info->prefix.'-reg-tal',
  'description' => 'Recruited Volunteer',
  'clawPackageType' => clawPackageType::event_talent,
  'isMainEvent' => true,
  'couponValue' => 100,
  'eventId' => self::$eventIds[$prefix.'-staff-recruited']->id,
  'category' => self::$categoryIds['staff-recruited']->id,
  'minShifts' => 0,
  'requiresCoupon' => true,
  'couponAccessGroups' => ['Super Users', 'Administrator']
]);

// $events[] = new clawEvent((object)[
//   'couponKey' => 'U',
//   'link' => $info->prefix.'-reg-vol1',
//   'description' => 'Volunteer 1 Shift',
//   'clawPackageType' => clawPackageType::volunteer1,
//   'isMainEvent' => true,
//   'couponValue' => 100,
//   'eventId' => self::$eventIds[$prefix.'-volunteer-1']->id,
//   'category' => self::$categoryIds['volunteer']->id,
//   'minShifts' => 1,
//   'requiresCoupon' => true,
//   'couponAccessGroups' => ['Super Users', 'Administrator', 'VolunteerMgmr', 'SkillsMgmr']
// ]);

$events[] = new clawEvent((object)[
  'couponKey' => 'V',
  'link' => $info->prefix.'-reg-vol2',
  'description' => 'Volunteer 2 Shifts',
  'clawPackageType' => clawPackageType::volunteer2,
  'isMainEvent' => true,
  'couponValue' => 0,
  'eventId' => self::$eventIds[$prefix.'-volunteer-2']->id,
  'category' => self::$categoryIds['volunteer']->id,
  'minShifts' => 2,
  'requiresCoupon' => false,
  'couponAccessGroups' => ['Super Users', 'Administrator', 'VolunteerMgmr'],
  'authNetProfile' => true
]);

$events[] = new clawEvent((object)[
  'couponKey' => 'W',
  'link' => $info->prefix.'-reg-vol3',
  'description' => 'Volunteer 3 Shifts',
  'clawPackageType' => clawPackageType::volunteer3,
  'isMainEvent' => true,
  'couponValue' => 0,
  'eventId' => self::$eventIds[$prefix.'-volunteer-3']->id,
  'category' => self::$categoryIds['volunteer']->id,
  'minShifts' => 3,
  'requiresCoupon' => false,
  'couponAccessGroups' => [],
  'authNetProfile' => true,
]);

$events[] = new clawEvent((object)[
  'couponKey' => 'Y',
  'link' => $info->prefix.'-reg-super',
  'description' => 'Super Volunteer',
  'clawPackageType' => clawPackageType::volunteersuper,
  'isMainEvent' => true,
  'couponValue' => 1,
  'eventId' => self::$eventIds[$prefix.'-volunteer-super']->id,
  'category' => self::$categoryIds['volunteer']->id,
  'minShifts' => 6,
  'requiresCoupon' => true,
  'couponAccessGroups' => ['Super Users', 'Administrator', 'VolunteerMgmr'],
  'authNetProfile' => true,
]);

// $events[] = new clawEvent((object)[
//   'couponKey' => 'Q',
//   'link' => $info->prefix.'-reg-ven-asst',
//   'description' => 'Vendor Crew (extras)',
//   'clawPackageType' => clawPackageType::vendor_crew_asst,
//   'isMainEvent' => false,
//   'couponValue' => 239,
//   'eventId' => self::$eventIds[$prefix.'-vendorcrew']->id,
//   'category' => self::$categoryIds['vendorcrew']->id,
//   'minShifts' => 0,
//   'requiresCoupon' => true,
//   'couponAccessGroups' => ['Super Users', 'Administrator']
// ]);


$events[] = new clawEvent((object)[
  'couponKey' => 'R',
  'link' => $info->prefix.'-reg-ven',
  'description' => 'Vendor Crew',
  'clawPackageType' => clawPackageType::vendor_crew,
  'isMainEvent' => true,
  'couponValue' => 239,
  'eventId' => self::$eventIds[$prefix.'-vendorcrew']->id,
  'category' => self::$categoryIds['vendorcrew']->id,
  'minShifts' => 0,
  'requiresCoupon' => true,
  'couponAccessGroups' => ['Super Users', 'Administrator']
]);

$events[] = new clawEvent((object)[
  'couponKey' => 'E',
  'link' => $info->prefix.'-reg-edu',
  'description' => 'Educator',
  'clawPackageType' => clawPackageType::educator,
  'isMainEvent' => true,
  'couponValue' => 100,
  'eventId' => self::$eventIds[$prefix.'-educator']->id,
  'category' => self::$categoryIds['educator']->id,
  'minShifts' => 0,
  'requiresCoupon' => true,
  'couponAccessGroups' => ['Super Users', 'Administrator', 'SkillsMgmr']
]);

$events[] = new clawEvent((object)[
  'couponKey' => 'I',
  'link' => $info->prefix.'-reg-att',
  'description' => 'General Assistant',
  'clawPackageType' => clawPackageType::attendee,
  'isMainEvent' => false,
  'couponValue' => 80,
  'eventId' => self::$eventIds[$prefix.'-attendee']->id,
  'category' => self::$categoryIds['attendee']->id,
  'minShifts' => 0,
  'requiresCoupon' => false,
  'couponAccessGroups' => ['Super Users', 'Administrator', 'SkillsMgmr']
]);

$events[] = new clawEvent((object)[
  'couponKey' => 'N',
  'link' => $info->prefix.'-reg-att',
  'description' => 'CLAW Nation',
  'clawPackageType' => clawPackageType::attendee,
  'isMainEvent' => false,
  'couponValue' => 239,
  'eventId' => self::$eventIds[$prefix.'-attendee']->id,
  'category' => self::$categoryIds['attendee']->id,
  'minShifts' => 0,
  'requiresCoupon' => false,
  'couponAccessGroups' => ['Super Users', 'Administrator', 'CNMgmr']
]);

$events[] = new clawEvent((object)[
  'couponKey' => 'FRI',
  'link' => 'onsite',
  'description' => 'Friday Day Pass',
  'clawPackageType' => clawPackageType::day_pass_fri,
  'isMainEvent' => true,
  'couponValue' => 0,
  'eventId' => self::$eventIds[$prefix.'-daypass-fri']->id,
  'category' => self::$categoryIds['day-passes']->id,
  'minShifts' => 0,
  'requiresCoupon' => false,
  'couponAccessGroups' => []
]);

$events[] = new clawEvent((object)[
  'couponKey' => 'SAT',
  'link' => 'onsite',
  'description' => 'Saturday Day Pass',
  'clawPackageType' => clawPackageType::day_pass_sat,
  'isMainEvent' => true,
  'couponValue' => 0,
  'eventId' => self::$eventIds[$prefix.'-daypass-sat']->id,
  'category' => self::$categoryIds['day-passes']->id,
  'minShifts' => 0,
  'requiresCoupon' => false,
  'couponAccessGroups' => []
]);

$events[] = new clawEvent((object)[
  'couponKey' => 'SUN',
  'link' => 'onsite',
  'description' => 'Sunday Day Pass',
  'clawPackageType' => clawPackageType::day_pass_sun,
  'isMainEvent' => true,
  'couponValue' => 0,
  'eventId' => self::$eventIds[$prefix.'-daypass-sun']->id,
  'category' => self::$categoryIds['day-passes']->id,
  'minShifts' => 0,
  'requiresCoupon' => false,
  'couponAccessGroups' => []
]);
