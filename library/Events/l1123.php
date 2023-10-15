<?php

namespace ClawCorpLib\Events;

defined('_JEXEC') or die('Restricted access');

use ClawCorpLib\Enums\EventPackageTypes;
use ClawCorpLib\Events\AbstractEvent;

use ClawCorpLib\Lib\ClawEvent;
use ClawCorpLib\Lib\ClawEvents;
use ClawCorpLib\Enums\EventTypes;
use ClawCorpLib\Lib\EventInfo;

class l1123 extends AbstractEvent
{
  public function PopulateInfo(): EventInfo
  {
    return new EventInfo(
    description: 'Leather Getaway 23',
    location: 'Los Angeles, CA',
    locationAlias: 'westin-bonaventure',
    start_date: '2023-11-20 00:00:00', // Monday
    end_date: 'next week Tuesday', // Calculated
    prefix: 'L23',
    shiftPrefix: strtolower('l23-shift-lg-'),
    mainAllowed: true,
    cancelBy: '2023-11-19 00:00:00', // Varies too much to calculate
    eventType: EventTypes::main,
    timezone: 'America/Los_Angeles',
    active: true,
    onsiteActive: false,
    termsArticleId: 230,
    );
  }

  public function PopulateEvents(string $prefix, bool $quiet = false)
  {
    $prefix = strtolower($prefix);
    $base = 249;

    $this->AppendEvent(new ClawEvent((object)[
      'alias' => $prefix.'-thu-buffet',
      'badgeValue' => 'Thu',
      'couponKey' => 'F',
      'description' => 'Chosen Family Thanksgiving Dinner',
      'eventPackageType' => EventPackageTypes::buffet_thu,
      'isMainEvent' => false,
      'couponValue' => 85,
      'fee' => 85,
      'start' => 'thursday 4pm',
      'end' => 'thursday 6pm',
      'category' => ClawEvents::getCategoryId('buffet'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => ['Super Users', 'Administrator'],
      'isAddon' => true
    ], $quiet));
    
    $this->AppendEvent(new ClawEvent((object)[
      'alias' => $prefix.'-dinner',
      'couponKey' => 'D',
      'description' => 'International Leather Family Dinner',
      'eventPackageType' => EventPackageTypes::dinner,
      'isMainEvent' => false,
      'couponValue' => 115,
      'fee' => 115,
      'start' => 'saturday 7pm',
      'end' => 'saturday 9pm',
      'category' => ClawEvents::getCategoryId('dinner'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => ['Super Users', 'Administrator'],
      'isAddon' => true
    ], $quiet));
    
    $this->AppendEvent(new ClawEvent((object)[
      'alias' => $prefix.'-fri-breakfast',
      'badgeValue' => 'Fri',
      'description' => 'Friday Breakfast Seminar',
      'eventPackageType' => EventPackageTypes::brunch_fri,
      'isMainEvent' => false,
      'couponValue' => 0,
      'fee' => 50,
      'start' => 'friday 11am',
      'end' => 'friday 1pm',
      'category' => ClawEvents::getCategoryId('buffet-breakfast'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => ['Super Users', 'Administrator', 'CNMgmr'],
      'isAddon' => true
    ], $quiet));
    
    $this->AppendEvent(new ClawEvent((object)[
      'alias' => $prefix.'-fri-buffet',
      'badgeValue' => 'Fri',
      'couponKey' => 'G',
      'description' => 'BLUF Dinner Buffet',
      'eventPackageType' => EventPackageTypes::buffet_fri,
      'isMainEvent' => false,
      'couponValue' => 90,
      'fee' => 90,
      'start' => 'friday 7pm',
      'end' => 'friday 9pm',
      'category' => ClawEvents::getCategoryId('buffet'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => ['Super Users', 'Administrator'],
      'isAddon' => true
    ], $quiet));
    
    $this->AppendEvent(new ClawEvent((object)[
      'alias' => $prefix.'-sat-breakfast',
      'badgeValue' => 'Sat',
      'description' => 'Saturday Breakfast Seminar',
      'eventPackageType' => EventPackageTypes::brunch_sat,
      'isMainEvent' => false,
      'couponValue' => 0,
      'fee' => 50,
      'start' => 'saturday 11am',
      'end' => 'saturday 1pm',
      'category' => ClawEvents::getCategoryId('buffet-breakfast'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => ['Super Users', 'Administrator', 'CNMgmr'],
      'isAddon' => true
    ], $quiet));
    
    $this->AppendEvent(new ClawEvent((object)[
      'alias' => $prefix.'-sun-buffet',
      'badgeValue' => 'Sun',
      'couponKey' => 'H',
      'description' => 'Sunday Supper Buffet',
      'eventPackageType' => EventPackageTypes::buffet_sun,
      'isMainEvent' => false,
      'couponValue' => 90,
      'fee' => 90,
      'start' => 'sunday 7pm',
      'end' => 'sunday 9pm',
      'category' => ClawEvents::getCategoryId('buffet'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => ['Super Users', 'Administrator'],
      'isAddon' => true
    ], $quiet));
    
    $this->AppendEvent(new ClawEvent((object)[
      'alias' => $prefix.'-brunch',
      'badgeValue' => 'Sun',
      'couponKey' => 'B',
      'description' => 'Sunday LHOF Brunch',
      'eventPackageType' => EventPackageTypes::brunch_sun,
      'isMainEvent' => false,
      'couponValue' => 65,
      'fee' => 65,
      'start' => 'sunday noon',
      'end' => 'sunday 2pm',
      'category' => ClawEvents::getCategoryId('buffet-breakfast'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => ['Super Users', 'Administrator'],
      'isAddon' => true
    ], $quiet));
    
    $this->AppendEvent(new ClawEvent((object)[
      'alias' => $prefix.'-meals-combo-all',
      'description' => 'Meal Combo All',
      'eventPackageType' => EventPackageTypes::meal_combo_all,
      'isMainEvent' => false,
      'couponValue' => 0,
      'fee' => 500,
      'category' => ClawEvents::getCategoryId('meal-combos'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => [],
      'isAddon' => true
    ], $quiet));
    
    $this->AppendEvent(new ClawEvent((object)[
      'alias' => $prefix.'-meals-combo-dinners',
      'description' => 'Meal Combo Dinners',
      'eventPackageType' => EventPackageTypes::meal_combo_dinners,
      'isMainEvent' => false,
      'couponValue' => 0,
      'fee' => 300,
      'category' => ClawEvents::getCategoryId('meal-combos'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => [],
      'isAddon' => true
    ], $quiet));
    
    // Events (for coupon generation)
    $this->AppendEvent(new ClawEvent((object)[
      'alias' => $prefix.'-attendee',
      'couponKey' => 'A',
      'description' => 'Attendee',
      'eventPackageType' => EventPackageTypes::attendee,
      'isMainEvent' => true,
      'couponValue' => $base,
      'fee' => $base,
      'category' => ClawEvents::getCategoryId('attendee'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => ['Super Users', 'Administrator', 'CNMgmr']
    ], $quiet));
    
    $this->AppendEvent(new ClawEvent((object)[
      'alias' => $prefix.'-vip',
      'description' => 'VIP',
      'eventPackageType' => EventPackageTypes::vip,
      'isMainEvent' => true,
      'couponValue' => 0,
      'fee' => 1250,
      'category' => ClawEvents::getCategoryId('vip'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => []
    ], $quiet));
    
    $this->AppendEvent(new ClawEvent((object)[
      'alias' => $prefix.'-staff-coordinator',
      'couponKey' => 'C',
      'description' => 'Coordinator',
      'eventPackageType' => EventPackageTypes::claw_staff,
      'isMainEvent' => true,
      'couponValue' => 100,
      'fee' => 100,
      'category' => ClawEvents::getCategoryId('staff-coordinator'),
      'minShifts' => 0,
      'requiresCoupon' => true,
      'couponAccessGroups' => ['Super Users', 'Administrator']
    ], $quiet));
    
    $this->AppendEvent(new ClawEvent((object)[
      'alias' => $prefix.'-staff-onsite',
      'couponKey' => 'S',
      'description' => 'Onsite Staff',
      'eventPackageType' => EventPackageTypes::event_staff,
      'isMainEvent' => true,
      'couponValue' => 100,
      'fee' => 100,
      'category' => ClawEvents::getCategoryId('staff-onsite'),
      'minShifts' => 0,
      'requiresCoupon' => true,
      'couponAccessGroups' => ['Super Users', 'Administrator']
    ], $quiet));
    
    $this->AppendEvent(new ClawEvent((object)[
      'alias' => $prefix.'-staff-recruited',
      'couponKey' => 'T',
      'description' => 'Recruited Volunteer',
      'eventPackageType' => EventPackageTypes::event_talent,
      'isMainEvent' => true,
      'couponValue' => 100,
      'fee' => 100,
      'category' => ClawEvents::getCategoryId('staff-recruited'),
      'minShifts' => 0,
      'requiresCoupon' => true,
      'couponAccessGroups' => ['Super Users', 'Administrator']
    ], $quiet));
    
    $this->AppendEvent(new ClawEvent((object)[
      'alias' => $prefix.'-volunteer-2',
      'couponKey' => 'V',
      'description' => 'Volunteer 2 Shifts',
      'eventPackageType' => EventPackageTypes::volunteer2,
      'isMainEvent' => true,
      'couponValue' => 0,
      'fee' => 99,
      'category' => ClawEvents::getCategoryId('volunteer'),
      'minShifts' => 2,
      'requiresCoupon' => false,
      'couponAccessGroups' => ['Super Users', 'Administrator', 'VolunteerMgmr'],
      'authNetProfile' => true
    ], $quiet));
    
    $this->AppendEvent(new ClawEvent((object)[
      'alias' => $prefix.'-volunteer-3',
      'couponKey' => 'W',
      'description' => 'Volunteer 3 Shifts',
      'eventPackageType' => EventPackageTypes::volunteer3,
      'isMainEvent' => true,
      'couponValue' => 0,
      'fee' => 1,
      'category' => ClawEvents::getCategoryId('volunteer'),
      'minShifts' => 3,
      'requiresCoupon' => false,
      'couponAccessGroups' => [],
      'authNetProfile' => true,
    ], $quiet));
    
    $this->AppendEvent(new ClawEvent((object)[
      'alias' => $prefix.'-volunteer-super',
      'couponKey' => 'Y',
      'description' => 'Super Volunteer',
      'eventPackageType' => EventPackageTypes::volunteersuper,
      'isMainEvent' => true,
      'couponValue' => 1,
      'fee' => 2,
      'category' => ClawEvents::getCategoryId('volunteer'),
      'minShifts' => 6,
      'requiresCoupon' => true,
      'couponAccessGroups' => ['Super Users', 'Administrator', 'VolunteerMgmr'],
      'authNetProfile' => true,
    ], $quiet));
    
    $this->AppendEvent(new ClawEvent((object)[
      'alias' => $prefix.'-vendorcrew-extra',
      'couponKey' => 'Q',
      'description' => 'Vendor Crew (extra)',
      'eventPackageType' => EventPackageTypes::vendor_crew_extra,
      'isMainEvent' => true,
      'couponValue' => $base,
      'fee' => $base,
      'category' => ClawEvents::getCategoryId('vendorcrew'),
      'minShifts' => 0,
      'requiresCoupon' => true,
      'couponAccessGroups' => ['Super Users', 'Administrator', 'VMMgmr']
    ], $quiet));
    
    $this->AppendEvent(new ClawEvent((object)[
      'alias' => $prefix.'-vendorcrew',
      'couponKey' => 'R',
      'description' => 'Vendor Crew',
      'eventPackageType' => EventPackageTypes::vendor_crew,
      'isMainEvent' => true,
      'couponValue' => $base,
      'fee' => $base,
      'category' => ClawEvents::getCategoryId('vendorcrew'),
      'minShifts' => 0,
      'requiresCoupon' => true,
      'couponAccessGroups' => ['Super Users', 'Administrator', 'VMMgmr']
    ], $quiet));
    
    $this->AppendEvent(new ClawEvent((object)[
      'alias' => $prefix.'-educator',
      'couponKey' => 'E',
      'description' => 'Educator',
      'eventPackageType' => EventPackageTypes::educator,
      'isMainEvent' => true,
      'couponValue' => 100,
      'fee' => 100,
      'category' => ClawEvents::getCategoryId('educator'),
      'minShifts' => 0,
      'requiresCoupon' => true,
      'couponAccessGroups' => ['Super Users', 'Administrator', 'SkillsMgmr']
    ], $quiet));
    
    $this->AppendEvent(new ClawEvent((object)[
      'alias' => $prefix.'-attendee',
      'couponKey' => 'N',
      'description' => 'CLAW Nation',
      'eventPackageType' => EventPackageTypes::attendee,
      'isMainEvent' => false,
      'couponValue' => $base,
      'couponOnly' => true,
      'fee' => $base,
      'category' => ClawEvents::getCategoryId('attendee'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => ['Super Users', 'Administrator', 'CNMgmr']
    ], $quiet));
    
    $this->AppendEvent(new ClawEvent((object)[
      'alias' => $prefix.'-daypass-fri',
      'description' => 'Friday Day Pass',
      'eventPackageType' => EventPackageTypes::day_pass_fri,
      'isMainEvent' => true,
      'couponValue' => 0,
      'fee' => 150,
      'start' => 'friday 9am',
      'end' => 'saturday 2am',
      'category' => ClawEvents::getCategoryId('day-passes'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => []
    ], $quiet));
    
    $this->AppendEvent(new ClawEvent((object)[
      'alias' => $prefix.'-daypass-sat',
      'couponKey' => 'SAT',
      'description' => 'Saturday Day Pass',
      'eventPackageType' => EventPackageTypes::day_pass_sat,
      'isMainEvent' => true,
      'couponValue' => 0,
      'fee' => 150,
      'start' => 'saturday 9am',
      'end' => 'sunday 2am',
      'category' => ClawEvents::getCategoryId('day-passes'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => []
    ], $quiet));
    
    $this->AppendEvent(new ClawEvent((object)[
      'alias' => $prefix.'-daypass-sun',
      'description' => 'Sunday Day Pass',
      'eventPackageType' => EventPackageTypes::day_pass_sun,
      'isMainEvent' => true,
      'couponValue' => 0,
      'fee' => 80,
      'start' => 'sunday 9am',
      'end' => 'next monday 2am',
      'category' => ClawEvents::getCategoryId('day-passes'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => []
    ], $quiet));
    
  }
}
