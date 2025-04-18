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
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Helpers\Locations;
use ClawCorpLib\Helpers\Schedule;
use ClawCorpLib\Lib\Sponsors;
use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Lib\EventInfo;

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

    $db = Factory::getContainer()->get('DatabaseDriver');
    $eventAlias =  $this->params->get('ScheduleEvent') ?: Aliases::current(true);

    $config = new Config($eventAlias);
    $this->adsdir = $config->getConfigText(ConfigFieldNames::CONFIG_IMAGES, 'ads', ' /images/0_static_graphics/ads');

    $this->locations = Locations::get($eventAlias);

    $this->sponsors = (new Sponsors(published: true))->sponsors;
    $schedule = new Schedule($eventAlias, $db);

    $this->eventInfo = new EventInfo($eventAlias);

    $dates = Helpers::getDateArray($this->eventInfo->start_date, true);

    $this->events = [];
    $this->start_date = '';
    $this->end_date = '';

    foreach ($dates as $date) {
      $this->events[$date] = [];

      $events = $schedule->getScheduleByDate($date);

      foreach ($events as $e) {
        $this->events[$date][] = $e;
      }

      // Set start/end dates
      // TODO: assumes continuous schedule events - is that what I want?
      if (count($this->events[$date]) > 0) {
        if (!$this->start_date) {
          $this->start_date = $date;
        }

        $this->end_date = $date;
      }
    }

    // Time zone
    date_default_timezone_set($this->eventInfo->timezone);
    $now = \date('Y-m-d');

    // Set default tab
    if ($this->eventInfo->onsiteActive && $now >= $this->start_date && $now <= $this->end_date) {
      $this->start_tab = date('D', strtotime($now));
    } else {
      $this->start_tab = date('D', strtotime($this->start_date));
    }

    # all caps $this->start_tab
    $this->start_tab = strtoupper($this->start_tab);

    parent::display($tpl);
  }
}
