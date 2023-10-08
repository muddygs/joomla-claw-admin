<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Site\View\Badgeprint;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Lib\Checkin;
use ClawCorpLib\Lib\Jwtwrapper;

/** @package ClawCorp\Component\Claw\Site\Controller */
class RawView extends BaseHtmlView
{
  public string $action;
  public string $registrationCode;
  public string $token;
  public string $page;
  public int $quantity;

  public bool $checkinRecord = false;
  public bool $primacy = true;
  public string $imagePath = '';

  protected array $registrationCodes = [];

  public function display($tpl = null)
  {
    $this->state = $this->get('State');

    Jwtwrapper::redirectOnInvalidToken(page: $this->page, token: $this->token);

    switch($this->action) {
      case 'print':
        $this->registrationCodes[] = $this->registrationCode;
        break;
      case 'printissue':
        $this->registrationCodes[] = $this->registrationCode;
        $this->checkinRecord = true;
        break;
      case 'printbatch':
        if ( $this->quantity <= 50 ) {
          $this->registrationCodes = Checkin::getUnprintedBadges($this->quantity);
        }
        break;

      default:
        echo 'Invalid action or registration code.';
        return; // TODO: Eh???
        break;
    }

    $event = Aliases::current();
    $this->imagePath = '/images/badges/' . $event . '/';
 
    parent::display($event);
  }
}
