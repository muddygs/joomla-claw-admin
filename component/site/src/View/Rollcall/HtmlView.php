<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2025 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Site\View\Rollcall;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Lib\EventConfig;
use ClawCorpLib\Lib\Jwtwrapper;

/** @package ClawCorp\Component\Claw\Site\Controller */
class HtmlView extends BaseHtmlView
{
  /**
   * Execute and display a template script.
   *
   * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
   *
   * @return  void
   */
  public function display($tpl = null)
  {
    $this->state = $this->get('State');

    $app = Factory::getApplication();
    $this->token = $app->input->get('token', '');
    Jwtwrapper::redirectOnInvalidToken(page: 'volunteer-roll-call', token: $this->token);

    $eventConfig = new EventConfig(Aliases::current(true));

    $shiftCatIds = array_merge($eventConfig->eventInfo->eb_cat_shifts, $eventConfig->eventInfo->eb_cat_supershifts);
    $rows = $eventConfig->getEventsByCategoryId($shiftCatIds);

    $this->shifts = [];
    foreach ($rows as $row) {
      $this->shifts[] = [
        'id' => $row->id,
        'title' => $row->title . " - {$row->total_registrants} / {$row->event_capacity}",
        'time' => $row->event_date,
        'total_registrants' => $row->total_registrants,
        'event_capacity' => $row->event_capacity
      ];
    }

    usort($this->shifts, function ($a, $b) {
      return strcmp($a['time'], $b['time']);
    });


    /** @var \Joomla\CMS\Extension\MVCComponent */
    $c = $app->bootComponent('com_claw');
    $d = $c->getMVCFactory();

    /** @var \ClawCorp\Component\Claw\Administrator\Model\ReportsModel */
    $model = $d->createModel('Reports', 'Administrator');
    $this->items = $model->getVolunteerOverview();

    parent::display();
  }
}
