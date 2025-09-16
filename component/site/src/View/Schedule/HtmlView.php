<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Site\View\Schedule;

defined('_JEXEC') or die;

use ClawCorpLib\Enums\ConfigFieldNames;
use ClawCorpLib\Helpers\Config;
use Joomla\CMS\Factory;
use Joomla\CMS\Date\Date;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Helpers\Locations;
use ClawCorpLib\Lib\Schedule;
use ClawCorpLib\Lib\Sponsors;
use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Lib\EventInfo;
use Exception;

/** @package ClawCorp\Component\Claw\Site\Controller */
class HtmlView extends BaseHtmlView
{
  public \ClawCorpLib\Lib\EventInfo $eventInfo;

  public function display($tpl = null)
  {
    /** @var \Joomla\CMS\Application\SiteApplication */
    $app = Factory::getApplication();

    $controllerMenuId = (int)Helpers::sessionGet('menuid');
    $menu = $app->getMenu()->getActive();
    if ($controllerMenuId != $menu->id) {
      $sitemenu = $app->getMenu();
      $sitemenu->setActive($controllerMenuId);
      $menu = $app->getMenu()->getActive();
    }
    $this->params = $menu->getParams();

    $eventAlias =  $this->params->get('ScheduleEvent') ?: Aliases::current(true);
    $this->eventInfo = new EventInfo($eventAlias);

    $config = new Config($eventAlias);
    $this->adsdir = $config->getConfigText(ConfigFieldNames::CONFIG_IMAGES, 'ads', ' /images/0_static_graphics/ads');

    $this->locations = Locations::get($eventAlias);

    $this->sponsors = (new Sponsors(published: true))->sponsors;

    // NOTE: The schedule class returns these ordered by datetime_start/datetime_end/featured
    $schedule = new Schedule($this->eventInfo);

    /** @var Iterator|Traversable|ArrayIterator<int, ScheduleRecord> */
    $iterator = $schedule->scheduleArray->getIterator();
    $success = $iterator->rewind();

    if (false === $success) {
      throw new Exception("An empty schedule cannot be displayed.");
    }

    $scheduleItem = $iterator->current();

    if (false === $scheduleItem) {
      throw new Exception("An empty schedule cannot be displayed.");
    }

    $this->start_date = $scheduleItem->datetime_start;

    $ranges = $this->getDateRanges();

    $this->events = [];

    foreach ($ranges as $date => $range) {
      $this->events[$date] = [];

      /** @var \Joomla\CMS\Date\Date */
      $start = $range->start;
      /** @var \Joomla\CMS\Date\Date */
      $end = $range->end;

      while ($iterator->valid() && $scheduleItem->datetime_start >= $start && $scheduleItem->datetime_start <= $end) {
        $this->events[$date][] = $scheduleItem;
        $iterator->next();
        $scheduleItem = $iterator->current();
      }

      // Always grab the latest ending date
      if (count($this->events[$date])) $this->end_date = end($this->events[$date])->datetime_start;
    }

    // Time zone
    date_default_timezone_set($this->eventInfo->timezone);
    $now = new Date();

    // Set default tab based on range for today if onsite is active (see: \ClawCorpLib\Lib\EventInfo)
    if ($this->eventInfo->onsiteActive && $now >= $this->start_date && $now <= $this->end_date) {
      $this->start_tab = date('D', strtotime($now));
    } else { // Otherwise, start at the beginning
      $this->start_tab = date('D', strtotime($this->start_date));
    }

    # all caps $this->start_tab
    $this->start_tab = strtoupper($this->start_tab);

    parent::display($tpl);
  }

  /**
   * Returns array with short day (Tue,Wed...Mon) to object of start/time Date classes
   */
  private function getDateRanges(): array
  {
    $result = [];

    if ($this->eventInfo->start_date->dayofweek != 1) // 0 is Sunday
    {
      die('Starting date must be a Monday');
    }

    $date = clone $this->eventInfo->start_date;
    $date->setTime(0, 0);

    for ($i = 0; $i < 7; $i++) {
      $date->modify(('+1 day'));

      $start = clone ($date);
      $end = clone ($date);
      $end->setTime(23, 59, 59);

      $result[$start->format('D')] = (object)[
        'start' => $start,
        'end' => $end,
      ];
    }

    return $result;
  }
}
