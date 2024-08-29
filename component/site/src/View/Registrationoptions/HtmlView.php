<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Site\View\Registrationoptions;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Lib\EventConfig;
use ClawCorpLib\Enums\EventPackageTypes;
use ClawCorpLib\Lib\Registrant;
use ClawCorpLib\Lib\RegistrantRecord;

/** @package ClawCorp\Component\Claw\Site\Controller */
class HtmlView extends BaseHtmlView
{
  public \Joomla\CMS\Application\SiteApplication $app;
  public string $eventAlias;
  public string $action;
  public string $prefix;
  public EventConfig $eventConfig;
  public string $tab = 'Meals';
  public bool $addons = false;
  public bool $vipRedirect = false;
  public ?EventPackageTypes $eventPackageType;
  private int $uid;
  public ?Registrant $registrant;
  public ?RegistrantRecord $mainEvent;

  public function __construct($config = [])
  {
    parent::__construct($config);
    
    $this->isAuthenticated();

    /** @var \Joomla\CMS\Application\SiteApplication */
    $this->app = Factory::getApplication();

    $input = $this->app->getInput();
    $this->eventAlias = $input->get('event', '', 'STRING');
    $this->action = $input->get('action', '', 'STRING');

    $activeEvents = EventConfig::getActiveEventAliases(mainOnly: true);
    if (!in_array($this->eventAlias, $activeEvents)) {
      $this->eventAlias = Aliases::current(true);
    }

    $this->eventConfig = new EventConfig($this->eventAlias);
    $this->registrant = new Registrant($this->eventAlias, $this->uid);
    $this->mainEvent = $this->registrant->getMainEvent();

    Helpers::sessionSet('eventAlias', $this->eventAlias);
    Helpers::sessionSet('eventAction', $this->action);
    Helpers::sessionSet('filter_duration', '');

    $this->eventPackageType = EventPackageTypes::tryFrom($this->action);
    $this->setDefaultTab();
    $this->addons = EventPackageTypes::addons == $this->eventPackageType;
  }

  private function setDefaultTab()
  {
    if (in_array($this->eventPackageType, [
      EventPackageTypes::volunteer2,
      EventPackageTypes::volunteer3,
      EventPackageTypes::volunteersuper,
    ])) {
      $this->tab = 'Shifts';
    }
  }

  public function display($tpl = null)
  {
    if (is_null($this->eventPackageType) || $this->eventPackageType == EventPackageTypes::none) {
      parent::display('error');
      return;
    }

    if (!$this->eventConfig->eventInfo->onsiteActive) {
      $blockedPackageTypes = [
        EventPackageTypes::day_pass_fri,
        EventPackageTypes::day_pass_sat,
        EventPackageTypes::day_pass_sun,
      ];

      if (in_array($this->eventPackageType, $blockedPackageTypes)) {
        parent::display('blocked');
        return;
      }
    }

    if ($this->addons && is_null($this->mainEvent)) {
      parent::display('notregistered');
      return;
    }

    if (
      !$this->addons &&
      !is_null($this->mainEvent) &&
      $this->mainEvent->registrant->eventPackageType != $this->eventPackageType
    ) {
      parent::display('alreadyregistered');
      return;
    }

    parent::display($this->eventAlias);
  }

  private function isAuthenticated(): bool
  {
    $this->uid = Factory::getApplication()->getIdentity()->id;

    // Redirect to login page
    if (!$this->uid) {
      $return = \Joomla\CMS\Uri\Uri::getInstance()->toString();
      $url    = 'index.php?option=com_users&view=login';
      $url   .= '&return=' . base64_encode($return);
      $this->app->enqueueMessage('Please sign in to continue registration.', 'warning');
      $this->app->redirect($url);
    }

    return true;
  }
}
