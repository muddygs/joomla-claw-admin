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

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Helpers\Schedule;
use ClawCorpLib\Helpers\Sponsors;
use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Lib\ClawEvent;
use ClawCorpLib\Lib\ClawEvents;
use Joomla\CMS\Helper\ModuleHelper;

/** @package ClawCorp\Component\Claw\Site\Controller */
class HtmlView extends BaseHtmlView
{
  
  public function display($tpl = null)
  {
    $this->state = $this->get('State');
    $this->form  = $this->get('Form');
    $this->item  = $this->get('Item');
    
    $params = $this->params = $this->state->get('params');
    $temp = clone ($params);

    // Check that user is in the submission group
    /** @var \Joomla\CMS\Application\SiteApplication */
    $app = Factory::getApplication();

    $controllerMenuId = (int)Helpers::sessionGet('menuid');
    $menu = $app->getMenu()->getActive();
    if ($controllerMenuId != $menu->id) {
      $sitemenu = $app->getMenu();
      $sitemenu->setActive($controllerMenuId);
      $menu = $app->getMenu()->getActive();
    }
    $paramsMenu = $menu->getParams();
    $temp->merge($paramsMenu);
    $this->params = $temp;

    $db = Factory::getContainer()->get('DatabaseDriver');
    $eventAlias =  $this->params->get('ScheduleEvent') ?? Aliases::current;

    

    $this->locations = \ClawCorpLib\Helpers\Locations::GetLocationsList($db);
    $this->sponsors = new Sponsors();
    $schedule = new Schedule($eventAlias, $db);
    $event = new ClawEvents($eventAlias);
    $eventInfo = $event->getClawEventInfo();

    $dates = Helpers::getDateArray($eventInfo->start_date, true);

    $this->events = [];
    $this->start_date = '';
    $this->end_date = '';

    foreach ( $dates AS $date ) {
      $this->events[$date] = [];

      $events = $schedule->getScheduleByDate($date);

      foreach ( $events AS $e ) {
        $this->events[$date][] = $e;
      }

      // Set start/end dates
      // TODO: assumes continuous schedule events - is that what I want?
      if ( count($this->events[$date]) > 0) {
        if ( !$this->start_date ) {
          $this->start_date = $date;
        }

        $this->end_date = $date;
  
      }
    }

    // Set default tab
    if ( Aliases::onsiteActive ) {
      $this->start_tab = date('D', strtotime($dates[date('D')]));
    } else {
      $this->start_tab = date('D', strtotime($this->start_date));
    }

    # all caps $this->start_tab
    $this->start_tab = strtoupper($this->start_tab);

    
    parent::display($tpl);
  }
}
