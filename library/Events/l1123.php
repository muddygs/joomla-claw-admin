<?php

namespace ClawCorpLib\Events;

defined('_JEXEC') or die('Restricted access');

use ClawCorpLib\Enums\EventPackageTypes;
use ClawCorpLib\Events\AbstractEvent;

use ClawCorpLib\Lib\ClawEvent;
use ClawCorpLib\Lib\ClawEvents;
use ClawCorpLib\Enums\EventTypes;

class l1123 extends AbstractEvent
{
  public function PopulateInfo()
  {
    $info = (object)[];
    $info->description = 'Leather Getaway 23';
    $info->location = 'Los Angeles, CA';
    $info->locationAlias = 'westin-bonaventure';
    $info->start_date = '2023-11-20 00:00:00'; // Monday
    $info->end_date = 'next week Tuesday'; // Calculated
    $info->prefix = 'L23';
    $info->shiftPrefix = strtolower($info->prefix.'-shift-lg-');
    $info->mainAllowed = true;
    $info->cancelBy = '2023-11-19 00:00:00'; // Varies too much to calculate
    $info->eventType = EventTypes::main;
    $info->timezone = 'America/Los_Angeles';
    $info->active = true;
    return $info;
  }

  public function PopulateEvents(string $prefix)
  {
    $prefix = strtolower($prefix);
    $base = 249;

    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'D',
      'description' => 'International Leather Family Dinner',
      'clawPackageType' => EventPackageTypes::dinner,
      'isMainEvent' => false,
      'couponValue' => 115,
      'fee' => 115,
      'start' => 'saturday 7pm',
      'end' => 'saturday 9pm',
      'eventId' => ClawEvents::getEventId($prefix.'-dinner'),
      'category' => ClawEvents::getCategoryId('dinner'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => ['Super Users', 'Administrator'],
      'isAddon' => true
    ]));
    
    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'B',
      'description' => 'Sunday LHOF Brunch',
      'clawPackageType' => EventPackageTypes::brunch_sun,
      'isMainEvent' => false,
      'couponValue' => 65,
      'fee' => 65,
      'start' => 'sunday noon',
      'end' => 'sunday 2pm',
      'eventId' => ClawEvents::getEventId($prefix.'-brunch'),
      'category' => ClawEvents::getCategoryId('buffet-breakfast'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => ['Super Users', 'Administrator'],
      'isAddon' => true
    ]));
    
    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => '',
      'description' => 'Saturday Breakfast Seminar',
      'clawPackageType' => EventPackageTypes::brunch_sat,
      'isMainEvent' => false,
      'couponValue' => 0,
      'fee' => 50,
      'start' => 'saturday 11am',
      'end' => 'saturday 1pm',
      'eventId' => ClawEvents::getEventId($prefix.'-sat-breakfast'),
      'category' => ClawEvents::getCategoryId('buffet-breakfast'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => ['Super Users', 'Administrator', 'CNMgmr'],
      'isAddon' => true
    ]));
    
    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => '',
      'description' => 'Friday Breakfast Seminar',
      'clawPackageType' => EventPackageTypes::brunch_fri,
      'isMainEvent' => false,
      'couponValue' => 0,
      'fee' => 50,
      'start' => 'friday 11am',
      'end' => 'friday 1pm',
      'eventId' => ClawEvents::getEventId($prefix.'-fri-breakfast'),
      'category' => ClawEvents::getCategoryId('buffet-breakfast'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => ['Super Users', 'Administrator', 'CNMgmr'],
      'isAddon' => true
    ]));
    
    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'F',
      'description' => 'Chosen Family Thanksgiving Dinner',
      'clawPackageType' => EventPackageTypes::buffet_thu,
      'isMainEvent' => false,
      'couponValue' => 85,
      'fee' => 85,
      'start' => 'thursday 4pm',
      'end' => 'thursday 6pm',
      'eventId' => ClawEvents::getEventId($prefix.'-thu-buffet'),
      'category' => ClawEvents::getCategoryId('buffet'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => ['Super Users', 'Administrator'],
      'isAddon' => true
    ]));
    
    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'G',
      'description' => 'BLUF Dinner Buffet',
      'clawPackageType' => EventPackageTypes::buffet_fri,
      'isMainEvent' => false,
      'couponValue' => 90,
      'fee' => 90,
      'start' => 'friday 7pm',
      'end' => 'friday 9pm',
      'eventId' => ClawEvents::getEventId($prefix.'-fri-buffet'),
      'category' => ClawEvents::getCategoryId('buffet'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => ['Super Users', 'Administrator'],
      'isAddon' => true
    ]));
    
    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'H',
      'description' => 'Sunday Supper Buffet',
      'clawPackageType' => EventPackageTypes::buffet_sun,
      'isMainEvent' => false,
      'couponValue' => 90,
      'fee' => 90,
      'start' => 'sunday 7pm',
      'end' => 'sunday 9pm',
      'eventId' => ClawEvents::getEventId($prefix.'-sun-buffet'),
      'category' => ClawEvents::getCategoryId('buffet'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => ['Super Users', 'Administrator'],
      'isAddon' => true
    ]));
    
    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => '',
      'description' => 'Meal Combo All',
      'clawPackageType' => EventPackageTypes::meal_combo_all,
      'isMainEvent' => false,
      'couponValue' => 0,
      'fee' => 500,
      'eventId' => ClawEvents::getEventId($prefix.'-meals-combo-all'),
      'category' => ClawEvents::getCategoryId('meal-combos'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => [],
      'isAddon' => true
    ]));
    
    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => '',
      'description' => 'Meal Combo Dinners',
      'clawPackageType' => EventPackageTypes::meal_combo_dinners,
      'isMainEvent' => false,
      'couponValue' => 0,
      'fee' => 300,
      'eventId' => ClawEvents::getEventId($prefix.'-meals-combo-dinners'),
      'category' => ClawEvents::getCategoryId('meal-combos'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => [],
      'isAddon' => true
    ]));
    
    // Events (for coupon generation)
    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'A',
      'link' => $prefix.'-reg-att',
      'description' => 'Attendee',
      'clawPackageType' => EventPackageTypes::attendee,
      'isMainEvent' => true,
      'couponValue' => $base,
      'fee' => $base,
      'eventId' => ClawEvents::getEventId($prefix.'-attendee'),
      'category' => ClawEvents::getCategoryId('attendee'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => ['Super Users', 'Administrator', 'CNMgmr']
    ]));
    
    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => '',
      'link' => $prefix.'-reg-vip',
      'description' => 'VIP',
      'clawPackageType' => EventPackageTypes::vip,
      'isMainEvent' => true,
      'couponValue' => 0,
      'fee' => 1250,
      'eventId' => ClawEvents::getEventId($prefix.'-vip'),
      'category' => ClawEvents::getCategoryId('vip'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => []
    ]));
    
    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'C',
      'link' => $prefix.'-reg-claw',
      'description' => 'Coordinator',
      'clawPackageType' => EventPackageTypes::claw_staff,
      'isMainEvent' => true,
      'couponValue' => 100,
      'fee' => 100,
      'eventId' => ClawEvents::getEventId($prefix.'-staff-coordinator'),
      'category' => ClawEvents::getCategoryId('staff-coordinator'),
      'minShifts' => 0,
      'requiresCoupon' => true,
      'couponAccessGroups' => ['Super Users', 'Administrator']
    ]));
    
    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'S',
      'link' => $prefix.'-reg-sta',
      'description' => 'Onsite Staff',
      'clawPackageType' => EventPackageTypes::event_staff,
      'isMainEvent' => true,
      'couponValue' => 100,
      'fee' => 100,
      'eventId' => ClawEvents::getEventId($prefix.'-staff-onsite'),
      'category' => ClawEvents::getCategoryId('staff-onsite'),
      'minShifts' => 0,
      'requiresCoupon' => true,
      'couponAccessGroups' => ['Super Users', 'Administrator']
    ]));
    
    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'T',
      'link' => $prefix.'-reg-tal',
      'description' => 'Recruited Volunteer',
      'clawPackageType' => EventPackageTypes::event_talent,
      'isMainEvent' => true,
      'couponValue' => 100,
      'fee' => 100,
      'eventId' => ClawEvents::getEventId($prefix.'-staff-recruited'),
      'category' => ClawEvents::getCategoryId('staff-recruited'),
      'minShifts' => 0,
      'requiresCoupon' => true,
      'couponAccessGroups' => ['Super Users', 'Administrator']
    ]));
    
    // $this->AppendEvent(new ClawEvent((object)[
    //   'couponKey' => 'U',
    //   'link' => $prefix.'-reg-vol1',
    //   'description' => 'Volunteer 1 Shift',
    //   'clawPackageType' => EventPackageTypes::volunteer1,
    //   'isMainEvent' => true,
    //   'couponValue' => 100,
    //   'eventId' => ClawEvents::getEventId($prefix.'-volunteer-1'),
    //   'category' => ClawEvents::getCategoryId('volunteer'),
    //   'minShifts' => 1,
    //   'requiresCoupon' => true,
    //   'couponAccessGroups' => ['Super Users', 'Administrator', 'VolunteerMgmr', 'SkillsMgmr']
    // ]));
    
    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'V',
      'link' => $prefix.'-reg-vol2',
      'description' => 'Volunteer 2 Shifts',
      'clawPackageType' => EventPackageTypes::volunteer2,
      'isMainEvent' => true,
      'couponValue' => 0,
      'fee' => 99,
      'eventId' => ClawEvents::getEventId($prefix.'-volunteer-2'),
      'category' => ClawEvents::getCategoryId('volunteer'),
      'minShifts' => 2,
      'requiresCoupon' => false,
      'couponAccessGroups' => ['Super Users', 'Administrator', 'VolunteerMgmr'],
      'authNetProfile' => true
    ]));
    
    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'W',
      'link' => $prefix.'-reg-vol3',
      'description' => 'Volunteer 3 Shifts',
      'clawPackageType' => EventPackageTypes::volunteer3,
      'isMainEvent' => true,
      'couponValue' => 0,
      'fee' => 1,
      'eventId' => ClawEvents::getEventId($prefix.'-volunteer-3'),
      'category' => ClawEvents::getCategoryId('volunteer'),
      'minShifts' => 3,
      'requiresCoupon' => false,
      'couponAccessGroups' => [],
      'authNetProfile' => true,
    ]));
    
    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'Y',
      'link' => $prefix.'-reg-super',
      'description' => 'Super Volunteer',
      'clawPackageType' => EventPackageTypes::volunteersuper,
      'isMainEvent' => true,
      'couponValue' => 1,
      'fee' => 2,
      'eventId' => ClawEvents::getEventId($prefix.'-volunteer-super'),
      'category' => ClawEvents::getCategoryId('volunteer'),
      'minShifts' => 6,
      'requiresCoupon' => true,
      'couponAccessGroups' => ['Super Users', 'Administrator', 'VolunteerMgmr'],
      'authNetProfile' => true,
    ]));
    
    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'Q',
      'link' => $prefix.'-reg-ven-extra',
      'description' => 'Vendor Crew (extra)',
      'clawPackageType' => EventPackageTypes::vendor_crew_extra,
      'isMainEvent' => true,
      'couponValue' => $base,
      'fee' => $base,
      'eventId' => ClawEvents::getEventId($prefix.'-vendorcrew-extra'),
      'category' => ClawEvents::getCategoryId('vendorcrew'),
      'minShifts' => 0,
      'requiresCoupon' => true,
      'couponAccessGroups' => ['Super Users', 'Administrator', 'VMMgmr']
    ]));
    
    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'R',
      'link' => $prefix.'-reg-ven',
      'description' => 'Vendor Crew',
      'clawPackageType' => EventPackageTypes::vendor_crew,
      'isMainEvent' => true,
      'couponValue' => $base,
      'fee' => $base,
      'eventId' => ClawEvents::getEventId($prefix.'-vendorcrew'),
      'category' => ClawEvents::getCategoryId('vendorcrew'),
      'minShifts' => 0,
      'requiresCoupon' => true,
      'couponAccessGroups' => ['Super Users', 'Administrator', 'VMMgmr']
    ]));
    
    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'E',
      'link' => $prefix.'-reg-edu',
      'description' => 'Educator',
      'clawPackageType' => EventPackageTypes::educator,
      'isMainEvent' => true,
      'couponValue' => 100,
      'fee' => 100,
      'eventId' => ClawEvents::getEventId($prefix.'-educator'),
      'category' => ClawEvents::getCategoryId('educator'),
      'minShifts' => 0,
      'requiresCoupon' => true,
      'couponAccessGroups' => ['Super Users', 'Administrator', 'SkillsMgmr']
    ]));
    
    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'N',
      'link' => $prefix.'-reg-att',
      'description' => 'CLAW Nation',
      'clawPackageType' => EventPackageTypes::attendee,
      'isMainEvent' => false,
      'couponValue' => $base,
      'fee' => $base,
      'eventId' => ClawEvents::getEventId($prefix.'-attendee'),
      'category' => ClawEvents::getCategoryId('attendee'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => ['Super Users', 'Administrator', 'CNMgmr']
    ]));
    
    // $this->AppendEvent(new ClawEvent((object)[
    //   'couponKey' => 'FRI',
    //   'link' => 'onsite',
    //   'description' => 'Friday Day Pass',
    //   'clawPackageType' => EventPackageTypes::day_pass_fri,
    //   'isMainEvent' => true,
    //   'couponValue' => 0,
    //   'fee' => 150,
    //   'start' => 'friday 9am',
    //   'end' => 'saturday 2am',
    //   'eventId' => ClawEvents::getEventId($prefix.'-daypass-fri'),
    //   'category' => ClawEvents::getCategoryId('day-passes'),
    //   'minShifts' => 0,
    //   'requiresCoupon' => false,
    //   'couponAccessGroups' => []
    // ]));
    
    // $this->AppendEvent(new ClawEvent((object)[
    //   'couponKey' => 'SAT',
    //   'link' => 'onsite',
    //   'description' => 'Saturday Day Pass',
    //   'clawPackageType' => EventPackageTypes::day_pass_sat,
    //   'isMainEvent' => true,
    //   'couponValue' => 0,
    //   'fee' => 150,
    //   'start' => 'saturday 9am',
    //   'end' => 'sunday 2am',
    //   'eventId' => ClawEvents::getEventId($prefix.'-daypass-sat'),
    //   'category' => ClawEvents::getCategoryId('day-passes'),
    //   'minShifts' => 0,
    //   'requiresCoupon' => false,
    //   'couponAccessGroups' => []
    // ]));
    
    // $this->AppendEvent(new ClawEvent((object)[
    //   'couponKey' => 'SUN',
    //   'link' => 'onsite',
    //   'description' => 'Sunday Day Pass',
    //   'clawPackageType' => EventPackageTypes::day_pass_sun,
    //   'isMainEvent' => true,
    //   'couponValue' => 0,
    //   'fee' => 80,
    //   'start' => 'sunday 9am',
    //   'end' => 'next monday 2am',
    //   'eventId' => ClawEvents::getEventId($prefix.'-daypass-sun'),
    //   'category' => ClawEvents::getCategoryId('day-passes'),
    //   'minShifts' => 0,
    //   'requiresCoupon' => false,
    //   'couponAccessGroups' => []
    // ]));
    
  }
}
