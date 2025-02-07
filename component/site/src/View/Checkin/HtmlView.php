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
use Joomla\CMS\Router\Route;

/** @package ClawCorp\Component\Claw\Site\Controller */
class HtmlView extends BaseHtmlView
{
  /**
   * Checkin options for badge check, badge print, or meals
   *
   * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
   *
   * @return  void
   */
  public function display($tpl = null)
  {
    $this->state = $this->get('State');
    $tpl = null;

    /** @var \Joomla\CMS\Application\SiteApplication */
    $app = Factory::getApplication();
    $this->token = $app->input->get('token', '');

    if ($this->token != '') {
      $jwt = new Jwtwrapper();
      $payload = $jwt->confirmToken($this->token, JwtStates::issued);

      if ($payload && property_exists($payload, 'state') && array_key_exists($payload->subject, Jwtwrapper::jwt_token_pages)) {
        $tpl = $payload->subject;
      }
    }

    $eventConfig = new EventConfig(Aliases::current(true));

    switch ($tpl) {
      case 'meals-checkin':
        $this->loadMealData($eventConfig);
        break;

      case 'badge-print':
        if ($eventConfig->eventInfo->badgePrintingOverride) {
          $tpl = 'badges_disabled';
        }
        break;

      case 'badge-checkin':
        break;

      default:
        $route = Route::_('index.php?option=com_claw&view=jwt');
        $app->redirect($route);
        break;
    }

    $this->setLayout($tpl); // no "default_" prefix
    parent::display();
  }

  private function loadMealData(EventConfig $eventConfig)
  {
    // Categories of interest
    // TODO: get the category titles from EB
    $mealHeadings = [
      $eventConfig->eventInfo->eb_cat_dinners => 'Leather Family Dinner',
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
}
