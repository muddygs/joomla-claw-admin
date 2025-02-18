<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Site\View\Mealcheckin;

defined('_JEXEC') or die;

use ClawCorpLib\Enums\EbPublishedState;
use ClawCorpLib\Enums\JwtStates;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Lib\EventConfig;
use ClawCorpLib\Lib\Jwtwrapper;

/** @package ClawCorp\Component\Claw\Site\Controller */
class HtmlView extends BaseHtmlView
{
  public array $meals = [];

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
      }
    }

    $eventConfig = new EventConfig(Aliases::current(true));
    $this->loadMealEventInfo($eventConfig);

    parent::display();
  }

  private function loadMealEventInfo(EventConfig $eventConfig)
  {
    // Categories of interest
    # TODO: could process to eliminate past events
    # TODO: get title from EB category directly
    $this->meals = [
      $eventConfig->eventInfo->eb_cat_dinners =>
      [
        'title' => 'Dinners',
        'eventIds' => []
      ],
      $eventConfig->eventInfo->eb_cat_buffets =>
      [
        'title' => 'Buffets',
        'eventIds' => []
      ],
      $eventConfig->eventInfo->eb_cat_brunches =>
      [
        'title' => 'Brunches',
        'eventIds' => []
      ],
    ];

    /** @var \ClawCorpLib\Lib\PackageInfo */
    foreach ($eventConfig->packageInfos as $packageInfo) {
      if ($packageInfo->published != EbPublishedState::published || $packageInfo->eventId == 0)
        continue;

      if (array_key_exists($packageInfo->category, $this->meals)) {
        $this->meals[$packageInfo->category]['packageIds'][$packageInfo->id] = $packageInfo->title;
      }
    }
  }
}
