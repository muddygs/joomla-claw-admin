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

use ClawCorpLib\Enums\EbCouponAssignments;
use ClawCorpLib\Enums\EbCouponTypes;
use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Lib\ClawEvent;
use ClawCorpLib\Lib\ClawEvents;
use ClawCorpLib\Lib\Coupons;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Model\FormModel;
use Joomla\Input\Json;

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
    
    foreach ( $tables AS $t ) {
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

  private function deltaTime($base, $time, $newbase): string
  {
    $base = Factory::getDate($base);
    $time = Factory::getDate($time);
    $newbase = Factory::getDate($newbase);

    $diff = $base->diff($time);

    if ( $diff === false ) {
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
        if ( str_contains($kk, 'eventid') !== false) {
          $grid->$k->$kk = 0;
        }
      }
    }

    return json_encode($grid);
  }
}
