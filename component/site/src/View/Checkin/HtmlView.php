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
use ClawCorpLib\Helpers\Helpers;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use ClawCorpLib\Lib\Aliases;
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
    $this->token = $app->input->get('token','','STRING');

    if ( $this->token != '' ) {
      $nonce = Jwtwrapper::getNonce();
      $jwt = new Jwtwrapper($nonce);
      $payload = $jwt->confirmToken($this->token, JwtStates::issued );

      if ($payload && property_exists($payload, 'state') && array_key_exists($payload->subject, Jwtwrapper::jwt_token_pages)) {
          $tpl = $payload->subject;
      }    
    }

    // If the user is super admin, allow database controls
    $user = $app->getIdentity();

    if ( $user->authorise('core.admin') ) {
      $this->state->set('user.admin', true);
      $this->records = Jwtwrapper::getJwtRecords();
    }

    parent::display($tpl);
  }
}
