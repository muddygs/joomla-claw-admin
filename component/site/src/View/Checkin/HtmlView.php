<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Site\View\Checkin;

defined('_JEXEC') or die;

use ClawCorpLib\Enums\JwtStates;
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
    $this->token = $app->input->get('token', '', 'STRING');

    if ($this->token != '') {
      $nonce = Jwtwrapper::getNonce();
      $jwt = new Jwtwrapper($nonce);
      $payload = $jwt->confirmToken($this->token, JwtStates::issued);

      if ($payload && property_exists($payload, 'state') && array_key_exists($payload->subject, Jwtwrapper::jwt_token_pages)) {
        $tpl = $payload->subject;
      }
    }

    // If the user is super admin, allow database controls
    $user = $app->getIdentity();
    $eventConfig = new EventConfig(Aliases::current(true));

    if ($user->authorise('core.admin')) {
      $this->state->set('user.admin', true);
      $this->records = Jwtwrapper::getJwtRecords();
    }

    // Prepare data for meals checkin
    if ('meals-checkin' == $tpl) {
      // Categories of interest
      $mealHeadings = [
        $eventConfig->eventInfo->eb_cat_dinners => 'International Leather Family Dinner',
        $eventConfig->eventInfo->eb_cat_buffets => 'Buffets',
        $eventConfig->eventInfo->eb_cat_brunches => 'Brunches'
      ];

      $this->meals = [];

      # TODO: could process to eliminate past events
      foreach ($mealHeadings as $catId => $desc) {
        $this->meals[-$catId] = $desc;
        /** @var \ClawCorpLib\Lib\PackageInfo */
        foreach ($eventConfig->packageInfos as $e) {
          if ($e->category == $catId) {
            $this->meals[$e->eventId] = '- ' . $e->title;
          }
        }
      }
    }

    if ('badge-print' == $tpl && $eventConfig->eventInfo->badgePrintingOverride) {
      $tpl = 'disabled';
    }

    if ('volunteer-roll-call' == $tpl) {
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
    }

    parent::display($tpl);
  }
}
