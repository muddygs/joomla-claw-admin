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
use ClawCorpLib\Lib\ClawEvents;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\FormModel;
use Joomla\Input\Json;
use ClawCorpLib\Lib\Ebmgmt;
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

    $reflection = new ReflectionClass("\\ClawCorpLib\\Events\\$from");
    /** @var \ClawCorpLib\Event\AbstractEvent */
    $instance = $reflection->newInstanceWithoutConstructor(); //Skip construction

    // Normal constructor does not call with quiet mode set to true
    /** @var \ClawCorpLib\Lib\EventInfo */
    $fromEventInfo = $instance->PopulateInfo();

    $reflection = new ReflectionClass("\\ClawCorpLib\\Events\\$to");
    /** @var \ClawCorpLib\Event\AbstractEvent */
    $instance = $reflection->newInstanceWithoutConstructor(); //Skip construction

    // Normal constructor does not call with quiet mode set to true
    /** @var \ClawCorpLib\Lib\EventInfo */
    $toEventInfo = $instance->PopulateInfo();

    // Do some database magic!

    $db = $this->getDatabase();

    $tables = [
      '#__claw_schedule',
      '#__claw_vendors',
      '#__claw_shifts',
    ];

    $results = [];

    foreach ($tables as $t) {
      // Delete existing
      $query = $db->getQuery(true);
      $query->delete($db->quoteName($t))
        ->where($db->quoteName('event') . ' = ' . $db->quote($to));
      $db->setQuery($query);
      $db->execute();
      
      // Copy from older event
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
    $log = [];

    $eventAlias = $json->get('jform[to_event]', '', 'string');

    // Validate events are valid
    if (!ClawEvents::isValidEventAlias($eventAlias)) {
      return 'Invalid to event: ' . $eventAlias;
    }

    // Ignore server-specific timezone information
    date_default_timezone_set('etc/UTC');

    $info = (object)[];
    $events = [];

    $reflection = new ReflectionClass("\\ClawCorpLib\\Events\\$eventAlias");
    /** @var \ClawCorpLib\Event\AbstractEvent */
    $instance = $reflection->newInstanceWithoutConstructor(); //Skip construction

    // Normal constructor does not call with quiet mode set to true
    /** @var \ClawCorpLib\Lib\EventInfo */
    $info = $instance->PopulateInfo();
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

    $article_id = $info->termsArticleId;

    /** @var \ClawCorpLib\Lib\ClawEvent */
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

      $insert = new ebMgmt($eventAlias, $event->category, $event->alias, $info->prefix . ' ' . $title, $event->description);
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
        $log[] =  "<p>Skipping existing: $title</p>";
      } else {
        $log[] =  "<p>Added: $title at event id $eventId</p>";
      }
    }
    return implode("\n", $log);
  }

  public function doCreateSpeedDating(Json $json): string
  {
    $log = [];

    $eventAlias = $json->get('jform[to_event]', '', 'string');

    // Validate events are valid
    if (!ClawEvents::isValidEventAlias($eventAlias)) {
      return 'Invalid to event: ' . $eventAlias;
    }

    $reflection = new ReflectionClass("\\ClawCorpLib\\Events\\$eventAlias");
    /** @var \ClawCorpLib\Event\AbstractEvent */
    $instance = $reflection->newInstanceWithoutConstructor(); //Skip construction

    // Normal constructor does not call with quiet mode set to true
    /** @var \ClawCorpLib\Lib\EventInfo */
    $info = $instance->PopulateInfo();
    /** @var stdObject */
    $config = $instance->Configs();

    // start and ending usability of these events
    $registration_start_date = Factory::getDate()->toSql();
    $publish_down = $this->modify($info->start_date, 'Friday 11PM');
    
    $article_id = $info->termsArticleId;
    $main_category_id = ClawEvents::getCategoryId('speed-dating');
    $capacity = 15;
    $waiting_list_capacity = 10;

    foreach ($config->speeddating as $key => $params) {
      foreach ($params->types as $type) {
        $dateObject = $info->getDate();

        $start = $dateObject->modify($params->date);
        $event_date = $start->toSql();
        $title = $info->prefix . ' ' . $key . ' (' . $type . ') ' . substr($params->date, 0, 3) . ' ' . $start->format('g:iA');

        $event_end_date = $start->modify('+45 minutes')->toSql();

        $alias = strtolower($info->prefix . '-' . preg_replace("/[^A-Za-z0-9]/", '', $key) . '-' . $type);

        $description = $params->description;
        $short_description = $params->description;

        $cut_off_date = $event_date;
        $cancel_before_date = $event_date;

        $insert = new ebMgmt($eventAlias, $main_category_id, $alias, $title, $description);
        $insert->set('activate_waiting_list', 1, false);
        $insert->set('article_id', $article_id, false);
        $insert->set('cancel_before_date', $cancel_before_date);
        $insert->set('cut_off_date', $cut_off_date);
        $insert->set('event_capacity', $capacity, false);
        $insert->set('event_date', $event_date);
        $insert->set('event_end_date', $event_end_date);
        $insert->set('publish_down', $publish_down);
        $insert->set('registration_start_date', $registration_start_date);
        $insert->set('short_description', $short_description);
        $insert->set('waiting_list_capacity', $waiting_list_capacity);

        $eventId = $insert->insert();
        if ($eventId == 0) {
          $log[] = "Skipping existing: $title";
        } else {
          $log[] = "Added: $title at event id $eventId";
        }
      }
    }

    return '<p>'.implode('</p><p>', $log).'</p>';
  }

  public function doCreateDiscountBundles(Json $json): string
  {
    $log = [];

    $eventAlias = $json->get('jform[to_event]', '', 'string');

    // Validate events are valid
    if (!ClawEvents::isValidEventAlias($eventAlias)) {
      return 'Invalid to event: ' . $eventAlias;
    }

    $reflection = new ReflectionClass("\\ClawCorpLib\\Events\\$eventAlias");
    /** @var \ClawCorpLib\Event\AbstractEvent */
    $instance = $reflection->newInstanceWithoutConstructor(); //Skip construction

    // Normal constructor does not call with quiet mode set to true
    /** @var \ClawCorpLib\Lib\EventInfo */
    $info = $instance->PopulateInfo();
    $instance->PopulateEvents($info->prefix, true);
    $events = $instance->getEvents();

    /** @var \ClawCorpLib\Lib\ClawEvent */
    foreach ( $events AS $event ) {
      if ( !$event->isVolunteer) continue;

      /** @var \ClawCorpLib\Lib\ClawEvent */
      foreach ( $events AS $mealBundleEvent ) {
        if ( $mealBundleEvent->bundleDiscount < 1 ) continue;

        $log[] = ebMgmt::addDiscountBundle([$event->eventId, $mealBundleEvent->eventId], $mealBundleEvent->bundleDiscount);
      }
    }

    return '<p>'.implode('</p><p>', $log).'</p>';
  }

  public function doCreateSponsorships(Json $json): string
  {
    $log = [];

    $eventAlias = $json->get('jform[to_event]', '', 'string');

    // Validate events are valid
    if (!ClawEvents::isValidEventAlias($eventAlias)) {
      return 'Invalid to event: ' . $eventAlias;
    }

    $reflection = new ReflectionClass("\\ClawCorpLib\\Events\\$eventAlias");
    /** @var \ClawCorpLib\Event\AbstractEvent */
    $instance = $reflection->newInstanceWithoutConstructor(); //Skip construction

    // Normal constructor does not call with quiet mode set to true
    /** @var \ClawCorpLib\Lib\EventInfo */
    $eventInfo = $instance->PopulateInfo();
    /** @var stdObject */
    $config = $instance->Configs();

    // start and ending usability of these events
    $registration_start_date = Factory::getDate()->toSql();
    $publish_down = $this->modify($eventInfo->start_date, '+8 days');

    $cancel_before_date = $this->modify($eventInfo->start_date, '-21 days');
    $cut_off_date = $eventInfo->start_date;
    $event_date = $eventInfo->start_date;
    $event_end_date = $eventInfo->end_date;

    $cards_due = date_format(date_create($this->modify($eventInfo->start_date, 'last saturday 11:59pm')), 'F j, Y');
    $graphics_due = date_format(date_create($cut_off_date), 'F j, Y');

    $user_email_body = <<< HTML
<p><b>INVOICE NUMBER: [INVOICE_NUMBER]</b></p>

<p><b>IMPORTANT DEADLINES</b></p>
<p><b>Yearbook graphics:</b> Send prepared graphics (see below) to graphics@clawinfo.org by {$graphics_due}</p>
<p><b>Run Bag Inserts:</b> {$cards_due} (see mailing address below)</p>

<p>Dear <strong>[FIRST_NAME] [LAST_NAME]</strong></p>
<p>You have just registered for event <strong>[EVENT_TITLE]</strong>. The registration detail is as follow :</p>
<p>[REGISTRATION_DETAIL]</p>
<p>Regards,</p>
<p>CLAW Guest Services</p>
<p>If you need assistance, contact us at <a href="https://www.clawinfo.org/help">Guest Services Link</a></p>

<p><a href="https://www.clawinfo.org/index.php?option=com_content&view=article&layout=html&id={$eventInfo->termsArticleId}">Terms &amp; Conditions</a></p>
<hr>
<h1>Run Bag Inserts</h1>
<p>Send 2,500 pieces to be received by Saturday, April 1, 2023 to:</p>
<p>
CLAW Corp.<br>
1549 Superior Ave. #1515<br>
Cleveland, OH 44114
</p>

<h1>Yearbook Specifications:</h1>
<p>Preferred formats: ai, jpg, tif, png or pdf format, <b><u>and at least 300 DPI.</u></b></p>
<p>Send ads to <a href="mailto:graphics@clawinfo.org">graphics@clawinfo.org</a>.</p>

<h2>Full Page Specifications</h2>
<p>Bleed size - 8 3/4&quot; H x 5 3/4&quot; W (1/8&quot; bleed - this will be the total size)<br />
Trim size - 8 1/2;&quot; H x 5 1/2;&quot; W (1/4;&quot; margin all around)<br />
Live area - 8&quot; H x 5&quot; W (Please keep all text and any graphics that you want to show within this area).</p>

<h2>Half Page Specifications</h2>
<p>3 7/8;&quot; H x 5&quot; W</p>

<h2>Quarter Page Specifications</h2>
<p>3 7/8;&quot; H x 2 3/8;&quot; W</p>

<h1>Digital Specifications</h1>
<p>Send graphics to <a href="mailto:graphics@clawinfo.org">graphics@clawinfo.org</a>.</p>

<h2>Website Banner</h2>
<p>940x200 pixels, jpg only</p>

<h2>E-blast Banner</h2>
<p>600x125 pixels, jpg only</p>

<h2>Leather Getaway Mobile App</h2>
<p>600x200 pixels, jpg only</p>
HTML;

    foreach ($config->sponsorships as $info) {
      if ($info->main_category_id == 0) continue;

      $alias = strtolower($eventInfo->prefix . '_spo_' . preg_replace("/[^A-Za-z0-9]+/", '_', $info->title));
      $title = $eventInfo->description . ' Sponsorship - ' . $info->title;


      $description = $info->description;
      $short_description = $info->description;

      $insert = new ebMgmt($eventAlias, $info->main_category_id, $alias, $title, $description);
      $insert->set('article_id', $eventInfo->termsArticleId, false);
      $insert->set('cancel_before_date', $cancel_before_date);
      $insert->set('cut_off_date', $cut_off_date);
      $insert->set('event_capacity', $info->event_capacity, false);
      $insert->set('event_date', $event_date);
      $insert->set('event_end_date', $event_end_date);
      $insert->set('registration_start_date', $registration_start_date);
      $insert->set('publish_down', $publish_down);
      $insert->set('short_description', $short_description);
      $insert->set('individual_price', $info->individual_price);

      $insert->set('user_email_body', $user_email_body);
      $insert->set('user_email_body_offline', $user_email_body);

      $eventId = $insert->insert();
      //$eventId = 0;
      if ($eventId == 0) {
        $log[] = "Skipping existing: $title";
      } else {
        $log[] = "Added: $title at event id $eventId";
      }
    }
    return '<p>' . implode('</p><p>', $log) . '</p>';
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
