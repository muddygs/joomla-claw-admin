<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2025 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Site\View\Checkin;

defined('_JEXEC') or die;

use ClawCorpLib\Checkin\Record;
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
   * Checkin options for badge check or print
   *
   * @param   string  $tpl  Unused (needed for class compatibility)
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
    $this->record = new Record();
    parent::display();
  }
}
