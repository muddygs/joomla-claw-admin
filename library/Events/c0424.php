<?php

namespace ClawCorpLib\Events;

defined('_JEXEC') or die('Restricted access');

use ClawCorpLib\Events\AbstractEvent;

use ClawCorpLib\Lib\ClawEvent;
use ClawCorpLib\Lib\ClawEvents;
use ClawCorpLib\Enums\EventTypes;
use ClawCorpLib\Enums\EventPackageTypes;

class c0424 extends AbstractEvent
{
  public function PopulateInfo()
  {
    $info = (object)[];
    $info->description = 'CLAW 24';
    $info->location = 'Cleveland, OH';
    $info->locationAlias = 'renaissance-cleveland';
    $info->start_date = '2024-04-08 00:00:00'; // Monday
    $info->end_date = 'next week Tuesday'; // Calculated
    $info->prefix = 'C24';
    $info->shiftPrefix = strtolower($info->prefix . '-shift-cle-');
    $info->mainAllowed = true;
    $info->cancelBy = '2024-04-01 00:00:00'; // Varies too much to calculate
    $info->timezone = 'America/New_York';
    $info->eventType = EventTypes::main;
    $info->active = true;
    return $info;
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
      'eventId' => ClawEvents::getEventId($prefix . '-attendee', $quiet),
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
      'eventId' => ClawEvents::getEventId($prefix . '-vip', $quiet),
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
      'isMainEvent' => true,
      'couponValue' => 100,
      'fee' => 100,
      'eventId' => ClawEvents::getEventId($prefix . '-staff-coordinator', $quiet),
      'category' => ClawEvents::getCategoryId('staff-coordinator'),
      'minShifts' => 0,
      'requiresCoupon' => true,
      'couponAccessGroups' => ['Super Users', 'Administrator']
    ]));

    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'S',
      'alias' => $prefix . '-staff-onsite',
      'link' => $prefix . '-reg-sta',
      'description' => 'Onsite Staff',
      'clawPackageType' => EventPackageTypes::event_staff,
      'isMainEvent' => true,
      'couponValue' => 100,
      'fee' => 100,
      'eventId' => ClawEvents::getEventId($prefix . '-staff-onsite', $quiet),
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
      'isMainEvent' => true,
      'couponValue' => 100,
      'fee' => 100,
      'eventId' => ClawEvents::getEventId($prefix . '-staff-recruited', $quiet),
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
      'isMainEvent' => true,
      'couponValue' => 0,
      'fee' => 99,
      'eventId' => ClawEvents::getEventId($prefix . '-volunteer-2', $quiet),
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
      'isMainEvent' => true,
      'couponValue' => 0,
      'fee' => 1,
      'eventId' => ClawEvents::getEventId($prefix . '-volunteer-3', $quiet),
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
      'isMainEvent' => true,
      'couponValue' => 1,
      'fee' => 1,
      'eventId' => ClawEvents::getEventId($prefix . '-volunteer-super', $quiet),
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
      'eventId' => ClawEvents::getEventId($prefix . '-vendorcrew', $quiet),
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
      'isMainEvent' => true,
      'couponValue' => 100,
      'fee' => 100,
      'eventId' => ClawEvents::getEventId($prefix . '-educator', $quiet),
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
      'eventId' => ClawEvents::getEventId($prefix . '-attendee', $quiet),
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
      'start' => 'saturday 7pm',
      'end' => 'saturday 9pm',
      'eventId' => ClawEvents::getEventId($prefix . '-dinner', $quiet),
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
      'start' => 'sunday noon',
      'end' => 'sunday 2pm',
      'eventId' => ClawEvents::getEventId($prefix . '-brunch', $quiet),
      'category' => ClawEvents::getCategoryId('buffet-breakfast'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => ['Super Users', 'Administrator'],
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
      'start' => 'saturday 11am',
      'end' => 'saturday 1pm',
      'eventId' => ClawEvents::getEventId($prefix . '-sat-breakfast', $quiet),
      'category' => ClawEvents::getCategoryId('buffet-breakfast'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => ['Super Users', 'Administrator', 'CNMgmr'],
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
      'start' => 'friday 11am',
      'end' => 'friday 1pm',
      'eventId' => ClawEvents::getEventId($prefix . '-fri-breakfast', $quiet),
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
      'start' => 'friday 7pm',
      'end' => 'friday 9pm',
      'eventId' => ClawEvents::getEventId($prefix . '-fri-buffet', $quiet),
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
      'start' => 'sunday 7pm',
      'end' => 'sunday 9pm',
      'eventId' => ClawEvents::getEventId($prefix.'-sun-buffet', $quiet),
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
      'eventId' => ClawEvents::getEventId($prefix.'-meals-combo-all', $quiet),
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
      'eventId' => ClawEvents::getEventId($prefix.'-meals-combo-dinners', $quiet),
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
      'eventId' => ClawEvents::getEventId($prefix . '-daypass-fri', $quiet),
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
      'eventId' => ClawEvents::getEventId($prefix . '-daypass-sat', $quiet),
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
      'eventId' => ClawEvents::getEventId($prefix . '-daypass-sun', $quiet),
      'category' => ClawEvents::getCategoryId('day-passes'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => []
    ]));
    #endregion

  }
}
