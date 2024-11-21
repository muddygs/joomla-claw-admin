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

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\User\UserFactory;
use Joomla\CMS\Factory;

use ClawCorpLib\Enums\EbPublishedState;
use ClawCorpLib\Enums\EventPackageTypes;
use ClawCorpLib\Enums\PackageInfoTypes;
use ClawCorpLib\Helpers\Rsform;
use ClawCorpLib\Helpers\Volunteers;
use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Lib\Ebfield;
use ClawCorpLib\Lib\EventConfig;
use ClawCorpLib\Lib\Registrants;
use ClawCorpLib\Helpers\EventBooking;

/**
 * Methods to handle a list of records.
 *
 * @since  1.6
 */
class ReportsModel extends BaseDatabaseModel
{
  use \Joomla\Database\DatabaseAwareTrait;

  public EventConfig $eventConfig;

  public function __construct($config = array())
  {
    parent::__construct($config);
    $input = Factory::getApplication()->getInput();
    $reportEvent = $input->getArray([
      'jform' => [
        'report_event' => 'string',
      ]
    ]);
    $eventAlias = $reportEvent['jform']['report_event'] ?: Aliases::current(true);
    $this->eventConfig = new EventConfig($eventAlias, []);
  }

  public function getSpeedDatingItems(): array
  {
    $items = [];

    /** @var \ClawCorpLib\Lib\PackageInfo */
    foreach ($this->eventConfig->packageInfos as $packageInfo) {
      if (
        $packageInfo->packageInfoType != PackageInfoTypes::speeddating
        || $packageInfo->published != EbPublishedState::published
        || $packageInfo->eventId < 1
      ) continue;

      foreach ($packageInfo->meta as $meta) {
        $object = (object)[];
        $object->event_id = $meta->eventId;
        $object->title = $packageInfo->title . ' (' . $meta->role . ') - ' . $packageInfo->start->format('D g:i A');

        $registrants = Registrants::byEventId($meta->eventId, [EbPublishedState::published, EbPublishedState::waitlist]);
        $object->registrants = $registrants;
        $items[] = $object;
      }
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
      if (
        $event->packageInfoType != PackageInfoTypes::main
        || $event->published != EbPublishedState::published
        || $event->eventId < 1
      ) continue;

      $records = Registrants::byEventId($event->eventId);
      $fields = ['TSHIRT', 'TSHIRT_VOL'];

      /** @var \ClawCorpLib\Lib\Registrant */
      foreach ($records as $r) {
        $isVolunteer = false;

        $r->mergeFieldValues($fields);

        /** @var \ClawCorpLib\Lib\RegistrantRecord */
        foreach ($r->records() as $record) {
          switch ($record->registrant->eventPackageType) {
            case EventPackageTypes::claw_staff:
            case EventPackageTypes::claw_board:
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

          if (empty($size)) {
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

    $shifts = self::getShiftEventDetails($this->eventConfig->alias);

    $coordinators = [];

    foreach (array_keys($shifts) as $sid) {
      $info = self::getShiftInfo($sid);
      if (null == $info) continue;

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
   * Retrieve shift title and coordinator user ids
   * @param int $sid Shift ID
   * @return object Shift information or null
   */
  private function getShiftInfo(int $sid): ?object
  {
    /** @var \Joomla\Database\DatabaseDriver */
    $db = $this->getDatabase();
    $sid = $db->quote($sid);

    $query = $db->getQuery(true);
    $query->select($db->qn(['title', 'coordinators']))
      ->from($db->qn('#__claw_shifts'))
      ->where($db->qn('id') . '=' . $sid);

    $db->setQuery($query);
    $row = $db->loadObject();

    if (null == $row) return null;

    $row->coordinators = json_decode($row->coordinators);

    return $row;
  }

  /**
   * Given a key/value id/title array of shift events, loads the details
   * based on the event ids
   * @param string Event alias from which to pull shifts
   * @return array eb_event records based on input event ids
   */
  private function getShiftEventDetails(string $clawEventAlias): array
  {
    /** @var \Joomla\Database\DatabaseDriver */
    $db = $this->getDatabase();

    $eventConfig = new EventConfig($clawEventAlias);

    $shiftEvents = $eventConfig->getEventsByCategoryId(
      array_merge($eventConfig->eventInfo->eb_cat_shifts, $eventConfig->eventInfo->eb_cat_supershifts)
    );

    $eventIds = array_column($shiftEvents, 'id');

    if (sizeof($eventIds) == 0) return [];

    $e = implode(',', $eventIds);

    $option = [];

    $query = $db->getQuery(true);
    $query->select($db->qn(['id', 'title', 'alias', 'event_capacity', 'event_date', 'event_end_date', 'published']))
      ->select('( SELECT count(*) FROM #__eb_registrants r WHERE r.event_id = e.id AND r.published = 1 ) AS memberCount')
      ->from($db->qn('#__eb_events', 'e'))
      ->where($db->qn('id') . ' IN (' . $e . ')')
      ->order($db->qn('title'));

    $db->setQuery($query);
    $rows = $db->loadObjectList();

    foreach ($rows as $row) {
      if (!str_starts_with($row->alias, $eventConfig->eventInfo->shiftPrefix)) continue;

      $pattern = '/-(\d+)-/';

      if (preg_match($pattern, substr($row->alias, strlen($eventConfig->eventInfo->shiftPrefix)), $matches)) {
        $sid = $matches[1];
      } else {
        continue;
      }

      if (!array_key_exists($sid, $option)) {
        $option[$sid] = [];
      }

      $option[$sid][$row->id] = $row;
    }

    return $option;
  }

  private function findListCustomField(int $categoryId): ?object
  {
    $db = $this->getDatabase();

    $query = $db->getQuery(true);
    $query->select(['f.id', 'f.name'])
      ->from($db->quoteName('#__eb_fields', 'f'))
      ->join('INNER', $db->quoteName('#__eb_field_categories', 'fc'), 'fc.category_id = ' . $db->quote($categoryId))
      ->where('f.id = fc.field_id')
      ->where($db->quoteName('fieldtype') . '= \'List\'')
      ->where($db->quoteName('published') . '= 1');

    $db->setQuery($query);
    $result =  $db->loadObject();
    return $result;
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
    $dinnerEventId = 0;

    /** @var \ClawCorpLib\Lib\PackageInfo */
    foreach ($this->eventConfig->packageInfos as $packageInfo) {
      if (
        $packageInfo->eventPackageType == EventPackageTypes::dinner
        && $packageInfo->published == EbPublishedState::published
        && $packageInfo->eventId > 0
      ) {
        $catId = $packageInfo->category;
        $dinnerCustomField = $this->findListCustomField($catId);

        if ($dinnerCustomField) {
          $dinnerField = new Ebfield($dinnerCustomField->name);
          $dinnerEventId = $packageInfo->eventId;
          break;
        }
      }
    }

    // Data ordering
    $mealCategoryIds = [
      $this->eventConfig->eventInfo->eb_cat_dinners,
      $this->eventConfig->eventInfo->eb_cat_buffets,
      $this->eventConfig->eventInfo->eb_cat_brunches
    ];

    foreach ($mealCategoryIds as $catId) {
      /** @var \ClawCorpLib\Lib\PackageInfo */
      foreach ($this->eventConfig->packageInfos as $packageInfo) {
        if (
          $packageInfo->published != EbPublishedState::published
          || $packageInfo->category != $catId
          || $packageInfo->eventId < 1
        ) continue;

        $subcount = [];

        if ($dinnerField && $packageInfo->eventId == $dinnerEventId) {
          $subcount = $dinnerField->valueCounts($packageInfo->eventId);
        }

        $items[$packageInfo->eventId] = (object)[
          'eventId' => $packageInfo->eventId,
          'description' => $packageInfo->title,
          'category' => $catId,
          'count' => Registrants::getRegistrantCount($packageInfo->eventId),
          'subcount' => $subcount,
        ];
      }
    }

    // Combo meals events
    foreach (
      [
        EventPackageTypes::combo_meal_1,
        EventPackageTypes::combo_meal_2,
        EventPackageTypes::combo_meal_3,
        EventPackageTypes::combo_meal_4
      ] as $comboMeal
    ) {
      /** @var \ClawCorpLib\Lib\PackageInfo */
      $packageInfo = $this->eventConfig->getPackageInfoByProperty('eventPackageType', $comboMeal, false);
      if (is_null($packageInfo)) continue;

      if ($dinnerField)
        $subcount = $dinnerField->valueCounts($packageInfo->eventId);

      foreach ($packageInfo->meta as $eventId) {
        $items[$eventId]->count += Registrants::getRegistrantCount($packageInfo->eventId);

        if ($eventId == $dinnerEventId && $dinnerEventId > 0) {
          foreach ($subcount as $count) {
            $comboValue = $count->field_value;
            $comboCount = $count->value_count;

            if (array_key_exists($comboValue, $items[$eventId]->subcount)) {
              $items[$eventId]->subcount[$comboValue]->value_count += $comboCount;
            } else {
              $items[$eventId]->subcount[$comboValue] = (object)[
                'field_value' => $comboValue,
                'value_count' => $comboCount,
              ];
            }
          }
        }
      }
    }

    $items['eventInfo'] = $this->eventConfig->eventInfo;

    return $items;
  }

  public function getArtShowSubmissions(): array
  {
    $items = [];
    $items['eventInfo'] = $this->eventConfig->eventInfo;
    $items['submissions'] = [];

    $formAlias = $this->eventConfig->eventInfo->alias . '-artshow';

    try {
      $form = new Rsform($this->getDatabase(), $formAlias);
    } catch (\Exception) {
      return $items;
    }

    $submissionsIds = $form->getSubmissionIds();

    if (is_null($submissionsIds)) return $items;

    foreach ($submissionsIds as $submissionId) {
      $submissionData = $form->getSubmissionData($submissionId);

      $data = (object)[];
      $data->submissionId = $submissionId;

      foreach ($submissionData as $field) {
        $data->{$field->FieldName} = $field->FieldValue;
      }

      $items['submissions'][$submissionId] = $data;
    }

    return $items;
  }

  public function getSpaSchedule(): array
  {
    /** Item info:
     * day -> therapist (userid) -> [
     *   start time (string)
     *   end time (string),
     *   deposit $ (float), 
     *   services (string),
     *   registrants (array of Registrant)
     * ]
     */

    $items = [];

    // Find full events and remove from $packageInfos
    $eventIds = [];

    /** @var \ClawCorpLib\Lib\PackageInfo */
    foreach ($this->eventConfig->packageInfos as $packageInfo) {
      if (
        $packageInfo->packageInfoType != PackageInfoTypes::spa
        || $packageInfo->published != EbPublishedState::published
        || $packageInfo->eventId < 1
      ) continue;

      foreach ($packageInfo->meta as $meta) {
        $eventIds[] = $meta->eventId;
      }
    }

    $capacityInfo = EventBooking::getEventsCapacityInfo($this->eventConfig->eventInfo, $eventIds);

    // Remove item from meta if missing or at capacity

    /** @var \ClawCorpLib\Lib\PackageInfo */
    foreach ($this->eventConfig->packageInfos as $packageInfo) {
      if (
        $packageInfo->packageInfoType != PackageInfoTypes::spa
        || $packageInfo->published != EbPublishedState::published
      ) continue;

      $startDay = $packageInfo->start->format('D');
      $startTime = $packageInfo->start->format('M:i A');
      $endTime = $packageInfo->end->format('M:i A');

      if (!array_key_exists($startDay, $items))
        $items[$startDay] = [];

      foreach ($packageInfo->meta as $meta) {
        if (!array_key_exists($meta->eventId, $capacityInfo)) continue;

        if (!array_key_exists($meta->userid, $items[$startDay])) {
          $items[$startDay][$meta->userid] = [];
        }

        if ($capacityInfo[$meta->eventId]->total_registrants > 0) {
          /** @var \ClawCorpLib\Lib\Registrant */
          $registrant = Registrants::byEventId($meta->eventId)[0];
          /** @var \ClawCorpLib\Lib\RegistrantRecord */
          $record = $registrant->records(true)[0];

          $session = [
            'start_time' => $startTime,
            'end_time' => $endTime,
            'deposit' => $packageInfo->fee,
            'services' => implode(',', $meta->services),
            'registrant' => [
              'name' => $record->registrant->first_name . ' ' . $record->registrant->last_name,
              'email' => $record->registrant->email,
            ]
          ];

          $items[$startDay][$meta->userid][] = $session;
        }
      }
    }

    return $items;
  }
}
