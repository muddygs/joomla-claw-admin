<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\Model;

\defined('_JEXEC') or die;

use ClawCorpLib\Enums\EbPublishedState;
use ClawCorpLib\Enums\EventPackageTypes;
use ClawCorpLib\Enums\PackageInfoTypes;
use ClawCorpLib\Helpers\Volunteers;
use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Lib\ClawEvents;
use ClawCorpLib\Lib\Ebfield;
use ClawCorpLib\Lib\EventConfig;
use ClawCorpLib\Lib\Registrants;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\User\UserFactory;
use Joomla\DI\Exception\KeyNotFoundException;
use Joomla\CMS\WebAsset\Exception\InvalidActionException;

/**
 * Methods to handle a list of records.
 *
 * @since  1.6
 */
class ReportsModel extends BaseDatabaseModel
{
  public EventConfig $eventConfig;

  public function __construct($config = array())
  {
    parent::__construct($config);

    $this->eventConfig = new EventConfig(Aliases::current(true));
  }

  public function getSpeedDatingItems(): array
  {
    $items = [];

    $events = $this->eventConfig->getEventsByCategoryId(ClawEvents::getCategoryIds(['speed-dating']));

    // Sort by event date
    usort($events, function ($a, $b) {
      return $a->event_date > $b->event_date;
    });

    foreach ($events as $event) {
      $items[$event->id] = (object)[];
      $items[$event->id]->event_id = $event->id;
      $items[$event->id]->title = $event->title;

      $registrants = Registrants::byEventId($event->id, [EbPublishedState::published, EbPublishedState::waitlist]);
      $items[$event->id]->registrants = $registrants;
    }

    return $items;
  }

  public function getShirtSizes(): array
  {
    $items = [];

    $sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL', 'None', 'Missing'];
    $items['sizes'] = $sizes;
    $items['missing'] = [];

    $counters = (object)[];
    $volcounters = (object)[];

    foreach ($sizes as $size) {
      $counters->$size = 0;
      $volcounters->$size = 0;
    }

    $totalCount = 0;
    $volTotalCount = 0;

    /** @var \ClawCorpLib\Lib\PackageInfo */
    foreach ($this->eventConfig->packageInfos as $event) {
      if ($event->packageInfoType != PackageInfoTypes::main ) continue;

      $records = Registrants::byEventId($event->eventId);
      $fields = ['TSHIRT', 'TSHIRT_VOL'];

      /** @var \ClawCorpLib\Lib\Registrant */
      foreach ($records as $r) {
        $isVolunteer = false;

        $r->mergeFieldValues($fields);

        /** @var \ClawCorpLib\Lib\RegistrantRecord */
        foreach ($r->records() as $record) {
          switch ( $record->registrant->eventPackageType ) {
            case EventPackageTypes::claw_staff:
            case EventPackageTypes::event_staff:
            case EventPackageTypes::event_talent:
            case EventPackageTypes::volunteer1:
            case EventPackageTypes::volunteer2:
            case EventPackageTypes::volunteer3:
            case EventPackageTypes::volunteersuper:
            case EventPackageTypes::educator:
              $size = $record->fieldValue->TSHIRT_VOL;
              $isVolunteer = true;
              break;
            case EventPackageTypes::attendee:
            case EventPackageTypes::vendor_crew:
            case EventPackageTypes::vendor_crew_extra:
            case EventPackageTypes::vip:
            case EventPackageTypes::vip2:
              $size = $record->fieldValue->TSHIRT;
              break;
            default:
              $size = '';
              break;
            }
            
            if ( empty($size) ) {
            $items['missing'][] = $record->registrant->invoice_number;
            $size = 'Missing';
          }

          $counters->$size++;
          if ($size != 'Missing' && $size != 'None') $totalCount++;

          if ($isVolunteer) {
            $volcounters->$size++;
            if ($size != 'Missing' && $size != 'None') $volTotalCount++;
            $isVolunteer = false;
          }
        }
      }
    }

    $items['eventInfo'] = $this->eventConfig->eventInfo;
    $items['counters'] = $counters;
    $items['volcounters'] = $volcounters;
    $items['totalCount'] = $totalCount;
    $items['volTotalCount'] = $volTotalCount;

    return $items;
  }

  public function getVolunteerOverview(): array
  {
    $items = [];
    $userFactory = new UserFactory($this->getDatabase());

    $shifts = Volunteers::getShiftEventDetails(Aliases::current(true));

    $coordinators = [];

    foreach ( array_keys($shifts) AS $sid ) {
      $info = Volunteers::getShiftInfo($sid);
      if ( null == $info ) continue;

      $primaryCoordinator = $info->coordinators[0];
      $user = $userFactory->loadUserById($primaryCoordinator);

      $coordinators[$sid] = [
        'title' => $info->title,
        'name' => $user->name,
        'email' => $user->email,
      ];
    }


    $items['eventInfo'] = $this->eventConfig->eventInfo;
    $items['shifts'] = $shifts;
    $items['coordinators'] = $coordinators;

    return $items;
  }

  /**
   * Parses meal events and returns counts. WARNING: Assumes "dinner" has only one event.
   * Dinner includes a subcount of meal types and counts.
   * @return array 
   * @throws KeyNotFoundException 
   * @throws InvalidActionException 
   */
  public function getMealCounts(): array
  {
    $items = [];

    $dinnerField = null;
    $dinnerField = new Ebfield('Dinner');
    $dinnerEventId = 0;

    // Data ordering
    $mealOrderCategory = ['dinner', 'buffet-breakfast', 'buffet'];

    foreach ( $mealOrderCategory AS $category ) {
      $catId = ClawEvents::getCategoryId($category);

      /** @var \ClawCorpLib\Lib\PackageInfo */
      foreach ( $this->eventConfig->packageInfos AS $packageInfo ) {
        if ( $packageInfo->category != $catId ) continue;

        $subcount = [];

        if ( 'dinner' == $category ) {
          $subcount = $dinnerField->valueCounts($packageInfo->eventId);
          $dinnerEventId = $packageInfo->eventId;
        }

        $items[$packageInfo->eventId] = (object)[
          'eventId' => $packageInfo->eventId,
          'description' => $packageInfo->title,
          'category' => $category,
          'count' => Registrants::getRegistrantCount($packageInfo->eventId),
          'subcount' => $subcount,
        ];

      }
    }

    // Combo meals events
    foreach ( [EventPackageTypes::combo_meal_1,
        EventPackageTypes::combo_meal_2, 
        EventPackageTypes::combo_meal_3, 
        EventPackageTypes::combo_meal_4] AS $comboMeal ) {
          /** @var \ClawCorpLib\Lib\PackageInfo */
      $packageInfo = $this->evenConfig->getClawEvent($comboMeal);
      if ( $packageInfo == null ) continue;

      $subcount = $dinnerField->valueCounts($packageInfo->eventId);

      foreach ( $packageInfo->meta AS $eventId ) {
        $items[$eventId]->count += Registrants::getRegistrantCount($packageInfo->eventId);

        if ( $eventId == $dinnerEventId && $dinnerEventId > 0 ) {
          foreach ( $subcount AS $count ) {
            $comboValue = $count->field_value;
            $comboCount = $count->value_count;

            foreach ( $items[$eventId]->subcount AS $itemCount ) {
              if ( $itemCount->field_value == $comboValue ) {
                $itemCount->value_count += $comboCount;
                break;
              }
            }
          }
        }
      }
    }

    return $items;
  }
}
