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

use ClawCorpLib\Enums\EbPublishedState;
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

    $this->setLayout($event);
    parent::display();
  }

  public function loadBatchRegistrationCodes()
  {
    $eventConfig = new EventConfig(Aliases::current(true));
    $this->registrationCodes = [];

    switch ($this->type) {
      case '0':
        $eventIds = $this->getOtherEventIds($eventConfig);
        break;
      case '1':
        $eventIds = $this->getAttendeeEventIds($eventConfig);
        break;
      case '2':
        $eventIds = $this->getVolunteerEventIds($eventConfig);
        break;
    }

    $this->registrationCodes = Checkin::getUnprintedBadges($eventIds, $this->quantity);
  }

  private function getAttendeeEventIds(EventConfig $eventConfig): array
  {
    $eventPackageTypes = [
      EventPackageTypes::attendee,
    ];

    return $this->typesToEventIds($eventConfig, $eventPackageTypes);
  }

  private function getVolunteerEventIds(EventConfig $eventConfig): array
  {
    $eventPackageTypes = [
      EventPackageTypes::volunteer1,
      EventPackageTypes::volunteer2,
      EventPackageTypes::volunteer3,
      EventPackageTypes::volunteersuper,
      EventPackageTypes::event_talent,
    ];

    return $this->typesToEventIds($eventConfig, $eventPackageTypes);
  }


  private function getOtherEventIds(EventConfig $eventConfig): array
  {
    $eventPackageTypes = [
      EventPackageTypes::claw_staff,
      EventPackageTypes::claw_board,
      EventPackageTypes::event_staff,
      EventPackageTypes::vendor_crew,
      EventPackageTypes::vendor_crew_extra,
      EventPackageTypes::educator,
      EventPackageTypes::vip,
    ];

    return $this->typesToEventIds($eventConfig, $eventPackageTypes);
  }

  private function typesToEventIds(EventConfig $eventConfig, array $types): array
  {
    $result = [];

    foreach ($types as $eventPackageType) {
      try {
        $packageInfo = $eventConfig->getMainEventByPackageType($eventPackageType);
      } catch (\Exception) {
        continue;
      }

      if ($packageInfo->published == EbPublishedState::published && $packageInfo->eventId != 0) {
        $result[] = $packageInfo->eventId;
      }
    }

    return $result;
  }
}
