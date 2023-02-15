<?php

namespace ClawCorpLib\Events;

defined('_JEXEC') or die('Restricted access');

use ClawCorpLib\Events\AbstractEvent;

use ClawCorpLib\Lib\ClawEvent;
use ClawCorpLib\Lib\ClawEvents;
use ClawCorpLib\Enums\EventTypes;
use ClawCorpLib\Enums\EventPackageTypes;

class c0423 extends AbstractEvent
{
  public function PopulateInfo()
  {
    $info = (object)[];
    $info->description = 'CLAW 23';
    $info->location = 'Cleveland, OH';
    $info->locationAlias = 'renaissance-cleveland';
    $info->start_date = '2023-04-03 00:00:00'; // Monday
    $info->end_date = 'next week Tuesday'; // Calculated
    $info->prefix = 'C23';
    $info->shiftPrefix = strtolower($info->prefix . '-shift-cle-');
    $info->mainAllowed = true;
    $info->cancelBy = '2023-04-01 00:00:00'; // Varies too much to calculate
    $info->eventType = EventTypes::main;
    return $info;
  }

  public function PopulateEvents(string $prefix)
  {
    $prefix = strtolower($prefix);

    // Addons (for coupon generation)
    $this->AppendEvent(new clawEvent((object)[
      'couponKey' => 'D',
      'description' => 'Dinner',
      'clawPackageType' => EventPackageTypes::dinner,
      'isMainEvent' => false,
      'couponValue' => 95,
      'eventId' => ClawEvents::getEventId($prefix . '-dinner'),
      'category' => ClawEvents::getCategoryId('dinner'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => ['Super Users', 'Administrator'],
      'isAddon' => true
    ]));

    $this->AppendEvent(new clawEvent((object)[
      'couponKey' => 'B',
      'description' => 'Sun Brunch',
      'clawPackageType' => EventPackageTypes::brunch_sun,
      'isMainEvent' => false,
      'couponValue' => 65,
      'eventId' => ClawEvents::getEventId($prefix . '-brunch'),
      'category' => ClawEvents::getCategoryId('buffet-breakfast'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => ['Super Users', 'Administrator'],
      'isAddon' => true
    ]));

    $this->AppendEvent(new clawEvent((object)[
      'couponKey' => 'G',
      'description' => 'Fri Buffet',
      'clawPackageType' => EventPackageTypes::buffet_fri,
      'isMainEvent' => false,
      'couponValue' => 90,
      'eventId' => ClawEvents::getEventId($prefix . '-fri-buffet'),
      'category' => ClawEvents::getCategoryId('buffet'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => ['Super Users', 'Administrator'],
      'isAddon' => true
    ]));

    $this->AppendEvent(new clawEvent((object)[
      'couponKey' => 'A',
      'link' => $prefix . '-reg-att',
      'description' => 'Attendee',
      'clawPackageType' => EventPackageTypes::attendee,
      'isMainEvent' => true,
      'couponValue' => 239,
      'eventId' => ClawEvents::getEventId($prefix . '-attendee'),
      'category' => ClawEvents::getCategoryId('attendee'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => ['Super Users', 'Administrator', 'CNMgmr']
    ]));

    $this->AppendEvent(new clawEvent((object)[
      'couponKey' => '',
      'link' => $prefix . '-reg-vip',
      'description' => 'VIP',
      'clawPackageType' => EventPackageTypes::vip,
      'isMainEvent' => true,
      'couponValue' => 0,
      'eventId' => ClawEvents::getEventId($prefix . '-vip'),
      'category' => ClawEvents::getCategoryId('vip'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => []
    ]));

    $this->AppendEvent(new clawEvent((object)[
      'couponKey' => 'C',
      'link' => $prefix . '-reg-claw',
      'description' => 'Coordinator',
      'clawPackageType' => EventPackageTypes::claw_staff,
      'isMainEvent' => true,
      'couponValue' => 100,
      'eventId' => ClawEvents::getEventId($prefix . '-staff-coordinator'),
      'category' => ClawEvents::getCategoryId('staff-coordinator'),
      'minShifts' => 0,
      'requiresCoupon' => true,
      'couponAccessGroups' => ['Super Users', 'Administrator']
    ]));

    $this->AppendEvent(new clawEvent((object)[
      'couponKey' => 'S',
      'link' => $prefix . '-reg-sta',
      'description' => 'Onsite Staff',
      'clawPackageType' => EventPackageTypes::event_staff,
      'isMainEvent' => true,
      'couponValue' => 100,
      'eventId' => ClawEvents::getEventId($prefix . '-staff-onsite'),
      'category' => ClawEvents::getCategoryId('staff-onsite'),
      'minShifts' => 0,
      'requiresCoupon' => true,
      'couponAccessGroups' => ['Super Users', 'Administrator']
    ]));

    $this->AppendEvent(new clawEvent((object)[
      'couponKey' => 'T',
      'link' => $prefix . '-reg-tal',
      'description' => 'Recruited Volunteer',
      'clawPackageType' => EventPackageTypes::event_talent,
      'isMainEvent' => true,
      'couponValue' => 100,
      'eventId' => ClawEvents::getEventId($prefix . '-staff-recruited'),
      'category' => ClawEvents::getCategoryId('staff-recruited'),
      'minShifts' => 0,
      'requiresCoupon' => true,
      'couponAccessGroups' => ['Super Users', 'Administrator']
    ]));

    $this->AppendEvent(new clawEvent((object)[
      'couponKey' => 'V',
      'link' => $prefix . '-reg-vol2',
      'description' => 'Volunteer 2 Shifts',
      'clawPackageType' => EventPackageTypes::volunteer2,
      'isMainEvent' => true,
      'couponValue' => 0,
      'eventId' => ClawEvents::getEventId($prefix . '-volunteer-2'),
      'category' => ClawEvents::getCategoryId('volunteer'),
      'minShifts' => 2,
      'requiresCoupon' => false,
      'couponAccessGroups' => ['Super Users', 'Administrator', 'VolunteerMgmr'],
      'authNetProfile' => true
    ]));

    $this->AppendEvent(new clawEvent((object)[
      'couponKey' => 'W',
      'link' => $prefix . '-reg-vol3',
      'description' => 'Volunteer 3 Shifts',
      'clawPackageType' => EventPackageTypes::volunteer3,
      'isMainEvent' => true,
      'couponValue' => 0,
      'eventId' => ClawEvents::getEventId($prefix . '-volunteer-3'),
      'category' => ClawEvents::getCategoryId('volunteer'),
      'minShifts' => 3,
      'requiresCoupon' => false,
      'couponAccessGroups' => [],
      'authNetProfile' => true,
    ]));

    $this->AppendEvent(new clawEvent((object)[
      'couponKey' => 'Y',
      'link' => $prefix . '-reg-super',
      'description' => 'Super Volunteer',
      'clawPackageType' => EventPackageTypes::volunteersuper,
      'isMainEvent' => true,
      'couponValue' => 1,
      'eventId' => ClawEvents::getEventId($prefix . '-volunteer-super'),
      'category' => ClawEvents::getCategoryId('volunteer'),
      'minShifts' => 6,
      'requiresCoupon' => true,
      'couponAccessGroups' => ['Super Users', 'Administrator', 'VolunteerMgmr'],
      'authNetProfile' => true,
    ]));

    $this->AppendEvent(new clawEvent((object)[
      'couponKey' => 'R',
      'link' => $prefix . '-reg-ven',
      'description' => 'Vendor Crew',
      'clawPackageType' => EventPackageTypes::vendor_crew,
      'isMainEvent' => true,
      'couponValue' => 239,
      'eventId' => ClawEvents::getEventId($prefix . '-vendorcrew'),
      'category' => ClawEvents::getCategoryId('vendorcrew'),
      'minShifts' => 0,
      'requiresCoupon' => true,
      'couponAccessGroups' => ['Super Users', 'Administrator']
    ]));

    $this->AppendEvent(new clawEvent((object)[
      'couponKey' => 'E',
      'link' => $prefix . '-reg-edu',
      'description' => 'Educator',
      'clawPackageType' => EventPackageTypes::educator,
      'isMainEvent' => true,
      'couponValue' => 100,
      'eventId' => ClawEvents::getEventId($prefix . '-educator'),
      'category' => ClawEvents::getCategoryId('educator'),
      'minShifts' => 0,
      'requiresCoupon' => true,
      'couponAccessGroups' => ['Super Users', 'Administrator', 'SkillsMgmr']
    ]));

    $this->AppendEvent(new clawEvent((object)[
      'couponKey' => 'I',
      'link' => $prefix . '-reg-att',
      'description' => 'General Assistant',
      'clawPackageType' => EventPackageTypes::attendee,
      'isMainEvent' => false,
      'couponValue' => 80,
      'eventId' => ClawEvents::getEventId($prefix . '-attendee'),
      'category' => ClawEvents::getCategoryId('attendee'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => ['Super Users', 'Administrator', 'SkillsMgmr']
    ]));

    $this->AppendEvent(new clawEvent((object)[
      'couponKey' => 'N',
      'link' => $prefix . '-reg-att',
      'description' => 'CLAW Nation',
      'clawPackageType' => EventPackageTypes::attendee,
      'isMainEvent' => false,
      'couponValue' => 239,
      'eventId' => ClawEvents::getEventId($prefix . '-attendee'),
      'category' => ClawEvents::getCategoryId('attendee'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => ['Super Users', 'Administrator', 'CNMgmr']
    ]));

    $this->AppendEvent(new clawEvent((object)[
      'couponKey' => 'FRI',
      'link' => 'onsite',
      'description' => 'Friday Day Pass',
      'clawPackageType' => EventPackageTypes::day_pass_fri,
      'isMainEvent' => true,
      'couponValue' => 0,
      'eventId' => ClawEvents::getEventId($prefix . '-daypass-fri'),
      'category' => ClawEvents::getCategoryId('day-passes'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => []
    ]));

    $this->AppendEvent(new clawEvent((object)[
      'couponKey' => 'SAT',
      'link' => 'onsite',
      'description' => 'Saturday Day Pass',
      'clawPackageType' => EventPackageTypes::day_pass_sat,
      'isMainEvent' => true,
      'couponValue' => 0,
      'eventId' => ClawEvents::getEventId($prefix . '-daypass-sat'),
      'category' => ClawEvents::getCategoryId('day-passes'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => []
    ]));

    $this->AppendEvent(new clawEvent((object)[
      'couponKey' => 'SUN',
      'link' => 'onsite',
      'description' => 'Sunday Day Pass',
      'clawPackageType' => EventPackageTypes::day_pass_sun,
      'isMainEvent' => true,
      'couponValue' => 0,
      'eventId' => ClawEvents::getEventId($prefix . '-daypass-sun'),
      'category' => ClawEvents::getCategoryId('day-passes'),
      'minShifts' => 0,
      'requiresCoupon' => false,
      'couponAccessGroups' => []
    ]));
  }
}
