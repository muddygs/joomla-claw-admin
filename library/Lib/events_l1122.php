<?php
namespace ClawCorpLib\Lib;

defined('_JEXEC') or die('Restricted access');

use ClawCorpLib\Lib\ClawEvent;
use ClawCorpLib\Lib\ClawEventTypes;
use ClawCorpLib\Lib\PackageTypes AS ClawPackageTypes;


$info->description = 'Leather Getaway 22';
$info->location = 'Los Angeles, CA';
$info->start_date = '2022-11-21 00:00:00'; // Monday
$info->end_date = '2022-11-29 00:00:00'; // Following Tuesday
$info->prefix = 'L22';
$info->shiftPrefix = strtolower($info->prefix.'-shift-lg-');
$info->mainAllowed = true;
$info->cancelBy = '';
$info->eventType = ClawEventTypes::main;

$prefix = strtolower($info->prefix);

// Addons (for coupon generation)
$events[] = new clawEvent((object)[
  'couponKey' => 'D',
  'description' => 'Dinner',
  'clawPackageType' => ClawPackageTypes::dinner,
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
  'clawPackageType' => ClawPackageTypes::brunch_sun,
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
//   'clawPackageType' => clawPackageTypes::brunch_sat,
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
//   'clawPackageType' => clawPackageTypes::brunch_fri,
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
//   'clawPackageType' => clawPackageTypes::buffet_thu,
//   'isMainEvent' => false,
//   'couponValue' => 0,
//   'eventId' => self::$eventIds[$prefix.'-wed-buffet']->id,
//   'category' => self::$categoryIds['buffet']->id,
//   'minShifts' => 0,
//   'requiresCoupon' => false,
//   'couponAccessGroups' => [],
//   'isAddon' => true
// ]);


$events[] = new clawEvent((object)[
  'couponKey' => 'F',
  'description' => 'Thu Buffet',
  'clawPackageType' => clawPackageTypes::buffet_thu,
  'isMainEvent' => false,
  'couponValue' => 80,
  'eventId' => self::$eventIds[$prefix.'-thu-buffet']->id,
  'category' => self::$categoryIds['buffet']->id,
  'minShifts' => 0,
  'requiresCoupon' => false,
  'couponAccessGroups' => ['Super Users', 'Administrator'],
  'isAddon' => true
]);

$events[] = new clawEvent((object)[
  'couponKey' => 'G',
  'description' => 'Fri Buffet',
  'clawPackageType' => clawPackageTypes::buffet_fri,
  'isMainEvent' => false,
  'couponValue' => 90,
  'eventId' => self::$eventIds[$prefix.'-fri-buffet']->id,
  'category' => self::$categoryIds['buffet']->id,
  'minShifts' => 0,
  'requiresCoupon' => false,
  'couponAccessGroups' => ['Super Users', 'Administrator'],
  'isAddon' => true
]);

$events[] = new clawEvent((object)[
  'couponKey' => 'H',
  'description' => 'Sun Buffet',
  'clawPackageType' => clawPackageTypes::buffet_sun,
  'isMainEvent' => false,
  'couponValue' => 90,
  'eventId' => self::$eventIds[$prefix.'-sun-buffet']->id,
  'category' => self::$categoryIds['buffet']->id,
  'minShifts' => 0,
  'requiresCoupon' => false,
  'couponAccessGroups' => ['Super Users', 'Administrator'],
  'isAddon' => true
]);

$events[] = new clawEvent((object)[
  'couponKey' => '',
  'description' => 'Meal Combo All',
  'clawPackageType' => clawPackageTypes::meal_combo_all,
  'isMainEvent' => false,
  'couponValue' => 0,
  'eventId' => self::$eventIds[$prefix.'-meals-combo-all']->id,
  'category' => self::$categoryIds['meal-combos']->id,
  'minShifts' => 0,
  'requiresCoupon' => false,
  'couponAccessGroups' => [],
  'isAddon' => true
]);

// $events[] = new clawEvent((object)[
//   'couponKey' => '',
//   'description' => 'Meal Combo Dinners',
//   'clawPackageType' => clawPackageTypes::meal_combo_dinners,
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
  'clawPackageType' => clawPackageTypes::attendee,
  'isMainEvent' => true,
  'couponValue' => 249,
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
  'clawPackageType' => clawPackageTypes::vip,
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
  'clawPackageType' => clawPackageTypes::claw_staff,
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
  'clawPackageType' => clawPackageTypes::event_staff,
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
  'clawPackageType' => clawPackageTypes::event_talent,
  'isMainEvent' => true,
  'couponValue' => 100,
  'eventId' => self::$eventIds[$prefix.'-staff-recruited']->id,
  'category' => self::$categoryIds['staff-recruited']->id,
  'minShifts' => 0,
  'requiresCoupon' => true,
  'couponAccessGroups' => ['Super Users', 'Administrator']
]);

$events[] = new clawEvent((object)[
  'couponKey' => 'U',
  'link' => $info->prefix.'-reg-vol1',
  'description' => 'Volunteer 1 Shift',
  'clawPackageType' => clawPackageTypes::volunteer1,
  'isMainEvent' => true,
  'couponValue' => 100,
  'eventId' => self::$eventIds[$prefix.'-volunteer-1']->id,
  'category' => self::$categoryIds['volunteer']->id,
  'minShifts' => 1,
  'requiresCoupon' => true,
  'couponAccessGroups' => ['Super Users', 'Administrator', 'VolunteerMgmr', 'SkillsMgmr']
]);

$events[] = new clawEvent((object)[
  'couponKey' => 'V',
  'link' => $info->prefix.'-reg-vol2',
  'description' => 'Volunteer 2 Shifts',
  'clawPackageType' => clawPackageTypes::volunteer2,
  'isMainEvent' => true,
  'couponValue' => 75,
  'eventId' => self::$eventIds[$prefix.'-volunteer-2']->id,
  'category' => self::$categoryIds['volunteer']->id,
  'minShifts' => 2,
  'requiresCoupon' => false,
  'couponAccessGroups' => ['Super Users', 'Administrator', 'VolunteerMgmr']
]);

$events[] = new clawEvent((object)[
  'couponKey' => 'W',
  'link' => $info->prefix.'-reg-vol3',
  'description' => 'Volunteer 3 Shifts',
  'clawPackageType' => clawPackageTypes::volunteer3,
  'isMainEvent' => true,
  'couponValue' => 100,
  'eventId' => self::$eventIds[$prefix.'-volunteer-3']->id,
  'category' => self::$categoryIds['volunteer']->id,
  'minShifts' => 3,
  'requiresCoupon' => true,
  'couponAccessGroups' => ['Super Users', 'Administrator', 'VolunteerMgmr']
]);

$events[] = new clawEvent((object)[
  'couponKey' => 'Y',
  'link' => $info->prefix.'-reg-super',
  'description' => 'Super Volunteer',
  'clawPackageType' => clawPackageTypes::volunteersuper,
  'isMainEvent' => true,
  'couponValue' => 100,
  'eventId' => self::$eventIds[$prefix.'-volunteer-super']->id,
  'category' => self::$categoryIds['volunteer']->id,
  'minShifts' => 6,
  'requiresCoupon' => true,
  'couponAccessGroups' => ['Super Users', 'Administrator', 'VolunteerMgmr']
]);


$events[] = new clawEvent((object)[
  'couponKey' => 'R',
  'link' => $info->prefix.'-reg-ven',
  'description' => 'Vendor Crew',
  'clawPackageType' => clawPackageTypes::vendor_crew,
  'isMainEvent' => true,
  'couponValue' => 249,
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
  'clawPackageType' => clawPackageTypes::educator,
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
  'description' => 'Ed Assistant',
  'clawPackageType' => clawPackageTypes::attendee,
  'isMainEvent' => false,
  'couponValue' => 114,
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
  'clawPackageType' => clawPackageTypes::attendee,
  'isMainEvent' => false,
  'couponValue' => 249,
  'eventId' => self::$eventIds[$prefix.'-attendee']->id,
  'category' => self::$categoryIds['attendee']->id,
  'minShifts' => 0,
  'requiresCoupon' => false,
  'couponAccessGroups' => ['Super Users', 'Administrator', 'CNMgmr']
]);

// $events[] = new clawEvent((object)[
//   'couponKey' => 'FRI',
//   'link' => 'onsite',
//   'description' => 'Friday Day Pass',
//   'clawPackageType' => clawPackageTypes::day_pass_fri,
//   'isMainEvent' => true,
//   'couponValue' => 0,
//   'eventId' => self::$eventIds[$prefix.'-daypass-fri']->id,
//   'category' => self::$categoryIds['day-passes']->id,
//   'minShifts' => 0,
//   'requiresCoupon' => false,
//   'couponAccessGroups' => []
// ]);

// $events[] = new clawEvent((object)[
//   'couponKey' => 'SAT',
//   'link' => 'onsite',
//   'description' => 'Saturday Day Pass',
//   'clawPackageType' => clawPackageTypes::day_pass_sat,
//   'isMainEvent' => true,
//   'couponValue' => 0,
//   'eventId' => self::$eventIds[$prefix.'-daypass-sat']->id,
//   'category' => self::$categoryIds['day-passes']->id,
//   'minShifts' => 0,
//   'requiresCoupon' => false,
//   'couponAccessGroups' => []
// ]);

// $events[] = new clawEvent((object)[
//   'couponKey' => 'SUN',
//   'link' => 'onsite',
//   'description' => 'Sunday Day Pass',
//   'clawPackageType' => clawPackageTypes::day_pass_sun,
//   'isMainEvent' => true,
//   'couponValue' => 0,
//   'eventId' => self::$eventIds[$prefix.'-daypass-sun']->id,
//   'category' => self::$categoryIds['day-passes']->id,
//   'minShifts' => 0,
//   'requiresCoupon' => false,
//   'couponAccessGroups' => []
// ]);
