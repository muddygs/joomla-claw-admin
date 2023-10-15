<?php

namespace ClawCorpLib\Events;

defined('_JEXEC') or die('Restricted access');

use ClawCorpLib\Events\AbstractEvent;

use ClawCorpLib\Lib\ClawEvent;
use ClawCorpLib\Lib\ClawEvents;
use ClawCorpLib\Enums\EventTypes;
use ClawCorpLib\Enums\EventPackageTypes;
use ClawCorpLib\Lib\EventInfo;

class c0423 extends AbstractEvent
{
  public function PopulateInfo(): EventInfo
  {
    return new EventInfo(
    description: 'CLAW 23',
    location: 'Cleveland, OH',
    locationAlias: 'renaissance-cleveland',
    start_date: '2023-04-03 00:00:00', // Monday
    end_date: 'next week Tuesday', // Calculated
    prefix: 'C23',
    shiftPrefix: strtolower('c23-shift-cle-'),
    mainAllowed: true,
    cancelBy: '2023-04-01 00:00:00', // Varies too much to calculate
    eventType: EventTypes::main,
    timezone: 'America/New_York',
    active: true,
    onsiteActive: false,
    termsArticleId: 77,
    );
  }

  public function PopulateEvents(string $prefix, bool $quiet = false)
  {
    $prefix = strtolower($prefix);

    // Addons (for coupon generation)
    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'D',
      'description' => 'Dinner',
      'eventPackageType' => EventPackageTypes::dinner,
      'isMainEvent' => false,
      'couponValue' => 95,
      'fee' => 0,
      'alias' => $prefix . '-dinner',
      'category' => ClawEvents::getCategoryId('dinner'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => ['Super Users', 'Administrator'],
      'isAddon' => true
    ]));

    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'B',
      'description' => 'Sun Brunch',
      'eventPackageType' => EventPackageTypes::brunch_sun,
      'isMainEvent' => false,
      'couponValue' => 65,
      'fee' => 0,
      'alias' => $prefix . '-brunch',
      'category' => ClawEvents::getCategoryId('buffet-breakfast'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => ['Super Users', 'Administrator'],
      'isAddon' => true
    ]));

    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'G',
      'description' => 'Fri Buffet',
      'eventPackageType' => EventPackageTypes::buffet_fri,
      'isMainEvent' => false,
      'couponValue' => 90,
      'fee' => 0,
      'alias' => $prefix . '-fri-buffet',
      'category' => ClawEvents::getCategoryId('buffet'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => ['Super Users', 'Administrator'],
      'isAddon' => true
    ]));

    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'A',
      'link' => $prefix . '-reg-att',
      'description' => 'Attendee',
      'eventPackageType' => EventPackageTypes::attendee,
      'isMainEvent' => true,
      'couponValue' => 239,
      'fee' => 0,
      'alias' => $prefix . '-attendee',
      'category' => ClawEvents::getCategoryId('attendee'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => ['Super Users', 'Administrator', 'CNMgmr']
    ]));

    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => '',
      'link' => $prefix . '-reg-vip',
      'description' => 'VIP',
      'eventPackageType' => EventPackageTypes::vip,
      'isMainEvent' => true,
      'couponValue' => 0,
      'fee' => 0,
      'alias' => $prefix . '-vip',
      'category' => ClawEvents::getCategoryId('vip'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => []
    ]));

    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'C',
      'link' => $prefix . '-reg-claw',
      'description' => 'Coordinator',
      'eventPackageType' => EventPackageTypes::claw_staff,
      'isMainEvent' => true,
      'couponValue' => 100,
      'fee' => 0,
      'alias' => $prefix . '-staff-coordinator',
      'category' => ClawEvents::getCategoryId('staff-coordinator'),
      'minShifts' => 0,
      'requiresCoupon' => true,
      'couponAccessGroups' => ['Super Users', 'Administrator']
    ]));

    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'S',
      'link' => $prefix . '-reg-sta',
      'description' => 'Onsite Staff',
      'eventPackageType' => EventPackageTypes::event_staff,
      'isMainEvent' => true,
      'couponValue' => 100,
      'fee' => 0,
      'alias' => $prefix . '-staff-onsite',
      'category' => ClawEvents::getCategoryId('staff-onsite'),
      'minShifts' => 0,
      'requiresCoupon' => true,
      'couponAccessGroups' => ['Super Users', 'Administrator']
    ]));

    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'T',
      'link' => $prefix . '-reg-tal',
      'description' => 'Recruited Volunteer',
      'eventPackageType' => EventPackageTypes::event_talent,
      'isMainEvent' => true,
      'couponValue' => 100,
      'fee' => 0,
      'alias' => $prefix . '-staff-recruited',
      'category' => ClawEvents::getCategoryId('staff-recruited'),
      'minShifts' => 0,
      'requiresCoupon' => true,
      'couponAccessGroups' => ['Super Users', 'Administrator']
    ]));

    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'V',
      'link' => $prefix . '-reg-vol2',
      'description' => 'Volunteer 2 Shifts',
      'eventPackageType' => EventPackageTypes::volunteer2,
      'isMainEvent' => true,
      'couponValue' => 0,
      'fee' => 0,
      'alias' => $prefix . '-volunteer-2',
      'category' => ClawEvents::getCategoryId('volunteer'),
      'minShifts' => 2,
      'requiresCoupon' => false,
      'couponAccessGroups' => ['Super Users', 'Administrator', 'VolunteerMgmr'],
      'authNetProfile' => true
    ]));

    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'W',
      'link' => $prefix . '-reg-vol3',
      'description' => 'Volunteer 3 Shifts',
      'eventPackageType' => EventPackageTypes::volunteer3,
      'isMainEvent' => true,
      'couponValue' => 0,
      'fee' => 0,
      'alias' => $prefix . '-volunteer-3',
      'category' => ClawEvents::getCategoryId('volunteer'),
      'minShifts' => 3,
      'requiresCoupon' => false,
      'couponAccessGroups' => [],
      'authNetProfile' => true,
    ]));

    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'Y',
      'link' => $prefix . '-reg-super',
      'description' => 'Super Volunteer',
      'eventPackageType' => EventPackageTypes::volunteersuper,
      'isMainEvent' => true,
      'couponValue' => 1,
      'fee' => 0,
      'alias' => $prefix . '-volunteer-super',
      'category' => ClawEvents::getCategoryId('volunteer'),
      'minShifts' => 6,
      'requiresCoupon' => true,
      'couponAccessGroups' => ['Super Users', 'Administrator', 'VolunteerMgmr'],
      'authNetProfile' => true,
    ]));

    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'R',
      'link' => $prefix . '-reg-ven',
      'description' => 'Vendor Crew',
      'eventPackageType' => EventPackageTypes::vendor_crew,
      'isMainEvent' => true,
      'couponValue' => 239,
      'fee' => 0,
      'alias' => $prefix . '-vendorcrew',
      'category' => ClawEvents::getCategoryId('vendorcrew'),
      'minShifts' => 0,
      'requiresCoupon' => true,
      'couponAccessGroups' => ['Super Users', 'Administrator']
    ]));

    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'E',
      'link' => $prefix . '-reg-edu',
      'description' => 'Educator',
      'eventPackageType' => EventPackageTypes::educator,
      'isMainEvent' => true,
      'couponValue' => 100,
      'fee' => 0,
      'alias' => $prefix . '-educator',
      'category' => ClawEvents::getCategoryId('educator'),
      'minShifts' => 0,
      'requiresCoupon' => true,
      'couponAccessGroups' => ['Super Users', 'Administrator', 'SkillsMgmr']
    ]));

    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'I',
      'link' => $prefix . '-reg-att',
      'description' => 'General Assistant',
      'eventPackageType' => EventPackageTypes::attendee,
      'isMainEvent' => false,
      'couponValue' => 80,
      'fee' => 0,
      'alias' => $prefix . '-attendee',
      'category' => ClawEvents::getCategoryId('attendee'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => ['Super Users', 'Administrator', 'SkillsMgmr']
    ]));

    $this->AppendEvent(new ClawEvent((object)[
      'couponKey' => 'N',
      'link' => $prefix . '-reg-att',
      'description' => 'CLAW Nation',
      'eventPackageType' => EventPackageTypes::attendee,
      'isMainEvent' => false,
      'couponValue' => 239,
      'couponOnly' => true,
      'fee' => 0,
      'alias' => $prefix . '-attendee',
      'category' => ClawEvents::getCategoryId('attendee'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => ['Super Users', 'Administrator', 'CNMgmr']
    ]));

    // $this->AppendEvent(new ClawEvent((object)[
    //   'couponKey' => 'FRI',
    //   'link' => 'onsite',
    //   'description' => 'Friday Day Pass',
    //   'eventPackageType' => EventPackageTypes::day_pass_fri,
    //   'isMainEvent' => true,
    //   'couponValue' => 0,
    //   'alias' => $prefix . '-daypass-fri',
    //   'category' => ClawEvents::getCategoryId('day-passes'),
    //   'minShifts' => 0,
    //   'requiresCoupon' => false,
    //   'couponAccessGroups' => []
    // ]));

    // $this->AppendEvent(new ClawEvent((object)[
    //   'couponKey' => 'SAT',
    //   'link' => 'onsite',
    //   'description' => 'Saturday Day Pass',
    //   'eventPackageType' => EventPackageTypes::day_pass_sat,
    //   'isMainEvent' => true,
    //   'couponValue' => 0,
    //   'alias' => $prefix . '-daypass-sat',
    //   'category' => ClawEvents::getCategoryId('day-passes'),
    //   'minShifts' => 0,
    //   'requiresCoupon' => false,
    //   'couponAccessGroups' => []
    // ]));

    // $this->AppendEvent(new ClawEvent((object)[
    //   'couponKey' => 'SUN',
    //   'link' => 'onsite',
    //   'description' => 'Sunday Day Pass',
    //   'eventPackageType' => EventPackageTypes::day_pass_sun,
    //   'isMainEvent' => true,
    //   'couponValue' => 0,
    //   'alias' => $prefix . '-daypass-sun',
    //   'category' => ClawEvents::getCategoryId('day-passes'),
    //   'minShifts' => 0,
    //   'requiresCoupon' => false,
    //   'couponAccessGroups' => []
    // ]));
  }
}
