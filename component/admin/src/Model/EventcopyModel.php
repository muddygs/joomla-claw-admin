<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2022 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\Model;

defined('_JEXEC') or die;

use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Lib\ClawEvent;
use ClawCorpLib\Lib\ClawEvents;
use ClawCorpLib\Lib\Coupons;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Model\FormModel;
use Joomla\Input\Json;
use ClawCorpLib\Lib\Ebmgmt;
use ClawCorpLib\Lib\EventInfo;
use DateTimeImmutable;
use ReflectionClass;

/**
 * Methods to handle a list of records.
 *
 * @since  1.6
 */
class EventcopyModel extends FormModel
{
  /**
   * The prefix to use with controller messages.
   *
   * @var    string
   * @since  1.6
   */
  protected $text_prefix = 'COM_CLAW_EVENTCOPY';

  /**
   * Method to get the record form.
   *
   * @param   array    $data      Data for the form.
   * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
   *
   * @return  Form|boolean  A Form object on success, false on failure
   *
   * @since   1.6
   */
  public function getForm($data = [], $loadData = false)
  {
    // Get the form.
    $form = $this->loadForm('com_claw.eventcopy', 'eventcopy', ['control' => 'jform', 'load_data' => $loadData]);

    if (empty($form)) {
      return false;
    }

    return $form;
  }

  public function doCopyEvent(Json $json): string
  {
    $from = $json->get('jform[from_event]', '', 'string');
    $to = $json->get('jform[to_event]', '', 'string');

    // Validate events are valid
    if (!ClawEvents::isValidEventAlias($from)) {
      return 'Invalid from event: ' . $from;
    }
    if (!ClawEvents::isValidEventAlias($to)) {
      return 'Invalid to event: ' . $to;
    }

    $fromEvent = new ClawEvents($from);
    $toEvent = new ClawEvents($to);

    $fromEventInfo = $fromEvent->getClawEventInfo();
    $toEventInfo = $toEvent->getClawEventInfo();

    // Do some database magic!

    $db = $this->getDatabase();

    $tables = [
      '#__claw_schedule',
      '#__claw_vendors',
      '#__claw_shifts',
    ];

    $results = [];

    foreach ($tables as $t) {
      $query = $db->getQuery(true);
      $query->select('*')
        ->from($db->quoteName($t))
        ->where($db->quoteName('event') . ' = ' . $db->quote($from));
      $db->setQuery($query);
      $rows = $db->loadObjectList();

      foreach ($rows as $x) {
        $x->event = $to;
        $x->id = null;

        switch ($t) {
          case '#__claw_schedule':
            $x->day = $this->deltaTime($fromEventInfo->start_date, $x->day, $toEventInfo->start_date);
            $x->poster = '';
            $x->poster_size = '';
            $x->event_id = 0;
            break;

          case '#__claw_shifts':
            $x->grid = $this->resetGrid($x->grid);
            break;
        }

        $x->mtime = Helpers::mtime();

        $db->insertObject($t, $x);
      }

      $results[$t] = "$t: " . count($rows) . ' rows copied';
    }

    return implode("<br/>", $results);
  }

  public function doCreateEvents(Json $json): string
  {
    // Ignore server-specific timezone information
    date_default_timezone_set('etc/UTC');

    $info = (object)[];
    $events = [];

    $reflection = new ReflectionClass("\\ClawCorpLib\\Events\\c0424");
    /** @var \ClawCorpLib\Event\AbstractEvent */
    $instance = $reflection->newInstanceWithoutConstructor(); //Skip construction

    // Normal constructor does not call with quiet mode set to true
    $info = new EventInfo($instance->PopulateInfo());
    $instance->PopulateEvents($info->prefix, true);
    $events = $instance->getEvents();

    // Base times to offset by "time" parameter for each event
    $cancel_before_date = $info->cancelBy;
    $startDate = $this->modify($info->start_date, 'Thursday 9AM');
    $endDate = $this->modify($info->start_date, 'next Monday midnight');;
    $cut_off_date = $endDate;

    // start and ending usability of these events
    $registration_start_date = Factory::getDate()->toSql();
    $publish_down = $this->modify($info->start_date, '+8 days');

    $article_id = 77; // Terms & Conditions

    foreach ($events as $event) {
      if ($event->alias == '') continue;

      $title = $event->description;

      $start = $startDate;
      $end = $endDate;
      $cutoff = $cut_off_date;
      if ($event->start != '') {
        $start = $this->modify($info->start_date, $event->start);
        $end = $this->modify($info->start_date, $event->end);

        $origin = new DateTimeImmutable($start);
        $target = new DateTimeImmutable($end);
        $interval = $origin->diff($target);
        if ($interval->h <= 8) $cutoff = $this->modify($start, '-3 hours');
      }

      $insert = new ebMgmt($event->category, $event->alias, $info->prefix . ' ' . $title, $event->description);
      $insert->set('article_id', $article_id, false);
      $insert->set('cancel_before_date', $cancel_before_date);
      $insert->set('cut_off_date', $cutoff);
      $insert->set('event_date', $start);
      $insert->set('event_end_date', $end);
      $insert->set('publish_down', $publish_down);

      $insert->set('individual_price', $event->fee);
      $insert->set('publish_down', $end);
      $insert->set('registration_start_date', $registration_start_date);
      $insert->set('payment_methods', 2); // Credit Cart

      $eventId = $insert->insert();
      if ($eventId == 0) {
        echo "<p>Skipping existing: $title</p>";
      } else {
        echo "<p>Added: $title at event id $eventId</p>";
      }
      return '';
    }
  }

  public function doCreateSpeedDating(Json $json): string
  {
    return '';
  }

  public function doCreateSponsorships(Json $json): string
  {
    return '';
  }

  private function deltaTime($base, $time, $newbase): string
  {
    $base = Factory::getDate($base);
    $time = Factory::getDate($time);
    $newbase = Factory::getDate($newbase);

    $diff = $base->diff($time);

    if ($diff === false) {
      throw new \Exception("Invalid date diff");
    }

    $newtime = $newbase->modify($diff->format('%R%d days'));

    return $newtime->toSql();
  }

  private function resetGrid(string $grid): string
  {
    $grid = json_decode($grid);

    foreach ($grid as $k => $v) {
      foreach (array_keys(get_object_vars($v)) as $kk) {
        if (str_contains($kk, 'eventid') !== false) {
          $grid->$k->$kk = 0;
        }
      }
    }

    return json_encode($grid);
  }

  private function modify(string $start_date, string $m): string
  {
    $date = Factory::getDate($start_date);
    return $date->modify($m)->toSql();
  }
}
