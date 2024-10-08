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

use ClawCorpLib\Enums\EventPackageTypes;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Lib\Checkin;
use ClawCorpLib\Lib\EventConfig;
use ClawCorpLib\Lib\EventInfo;
use ClawCorpLib\Lib\Jwtwrapper;
use Joomla\CMS\Factory;

/** @package ClawCorp\Component\Claw\Site\Controller */
class RawView extends BaseHtmlView
{
  public string $action;
  public string $registrationCode;
  public string $token;
  public string $page;
  public int $quantity;

  public bool $checkinRecord = false;
  public array $printOrderings = [];
  public string $imagePath = '';

  protected array $registrationCodes = [];

  public function display($tpl = null)
  {
    $this->state = $this->get('State');

    Jwtwrapper::redirectOnInvalidToken(page: $this->page, token: $this->token);

    switch ($this->action) {
      case 'print':
        $this->registrationCodes[] = $this->registrationCode;
        break;
      case 'printissue':
        $this->registrationCodes[] = $this->registrationCode;
        $this->checkinRecord = true;
        break;
      case 'printbatch':
        if ($this->quantity <= 50 && $this->quantity > 0) {
          $this->loadBatchRegistrationCodes();
        }
        break;

      default:
        echo 'Invalid action or registration code.';
        return; // TODO: Eh???
        break;
    }

    $event = Aliases::current(true);

    $eventInfo = new EventInfo($event);

    if ($eventInfo->badgePrintingOverride) {
      $event = 'disabled';
    }

    $this->imagePath = '/images/badges/' . $event . '/';

    // Load printing modes set in global configuration
    /** @var \Joomla\CMS\Application\SiteApplication */
    $app = Factory::getApplication();
    $params = $app->getParams();
    // $this->type array key
    $this->printOrderings[0] = $params->get('onsite_printer_others', 'sequential');
    $this->printOrderings[1] = $params->get('onsite_printer_attendee', 'sequential');
    $this->printOrderings[2] = $params->get('onsite_printer_volunteer', 'sequential');

    parent::display($event);
  }

  public function loadBatchRegistrationCodes(): array
  {
    $eventConfig = new EventConfig(Aliases::current(true));
    $this->registrationCodes = [];

    switch ($this->type) {
      case '0':
        $staff = $eventConfig->getMainEventByPackageType(EventPackageTypes::claw_staff)->eventId;
        $board = $eventConfig->getMainEventByPackageType(EventPackageTypes::claw_board)->eventId;
        $eventStaff = $eventConfig->getMainEventByPackageType(EventPackageTypes::event_staff)->eventId;
        $vendorCrew = $eventConfig->getMainEventByPackageType(EventPackageTypes::vendor_crew)->eventId;
        //$vendorCrewExtra = $eventConfig->getMainEventByPackageType(EventPackageTypes::vendor_crew_extra)->eventId;
        $educator = $eventConfig->getMainEventByPackageType(EventPackageTypes::educator)->eventId;
        $vip = $eventConfig->getMainEventByPackageType(EventPackageTypes::vip)->eventId;
        $this->registrationCodes = Checkin::getUnprintedBadges([$staff, $board, $eventStaff, $vendorCrew, $educator, $vip], $this->quantity);
        break;
      case '1':
        $attendee = $eventConfig->getMainEventByPackageType(EventPackageTypes::attendee);
        $this->registrationCodes = Checkin::getUnprintedBadges([$attendee->eventId], $this->quantity);
        break;
      case '2':
        $vol2 = $eventConfig->getMainEventByPackageType(EventPackageTypes::volunteer2)->eventId;
        $vol3 = $eventConfig->getMainEventByPackageType(EventPackageTypes::volunteer3)->eventId;
        $volSuper = $eventConfig->getMainEventByPackageType(EventPackageTypes::volunteersuper)->eventId;
        $volTalent = $eventConfig->getMainEventByPackageType(EventPackageTypes::event_talent)->eventId;
        $this->registrationCodes = Checkin::getUnprintedBadges([$vol2, $vol3, $volSuper, $volTalent], $this->quantity);
        break;
    }

    return $this->registrationCodes;
  }
}
