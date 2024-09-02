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
use Joomla\CMS\Application\SiteApplication;

use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Lib\EventConfig;
use ClawCorpLib\Enums\EventPackageTypes;
use ClawCorpLib\Lib\Registrant;
use ClawCorpLib\Lib\RegistrantRecord;
use ClawCorpLib\Lib\PackageInfo;
use ClawCorpLib\Helpers\Config;
use ClawCorpLib\Enums\ConfigFieldNames;

// *sigh* not namespaced
require_once(JPATH_ROOT . '/components/com_eventbooking/helper/cart.php');
require_once(JPATH_ROOT . '/components/com_eventbooking/helper/database.php');
require_once(JPATH_ROOT . '/components/com_eventbooking/helper/helper.php');

/** @package ClawCorp\Component\Claw\Site\Controller */
class HtmlView extends BaseHtmlView
{
  public ?SiteApplication $app;
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
  public ?PackageInfo $targetPackage;
  public string $registrationSurveyLink = '';
  public string $coupon = '';

  public function __construct($config = [])
  {
    parent::__construct($config);

    /** @var \Joomla\CMS\Application\SiteApplication */
    $this->app = Factory::getApplication();
    $this->isAuthenticated();

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

    $this->resetSession();

    $this->eventPackageType = EventPackageTypes::tryFrom($this->action);
    $this->targetPackage = $this->eventConfig->getPackageInfo($this->eventPackageType);
    $this->setDefaultTab();
    $this->resetCart();
    $this->addons = EventPackageTypes::addons == $this->eventPackageType;
    $this->eventDescription = !$this->addons ? $this->targetPackage->title . ' Registration' : $this->mainEvent->event->title . ' Addons';
    $this->registrationSurveyLink = Helpers::sessionGet('registrationSurveyLink', '/');

    $config = new Config($this->eventAlias);
    $this->shiftsBaseUrl = $config->getConfigText(ConfigFieldNames::CONFIG_URLPREFIX, 'shifts');

    $this->coupon = Helpers::sessionGet('clawcoupon');
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

  private function resetCart()
  {
    if ($this->addons || !is_null($this->mainEvent)) {
      return;
    }

    // Auto add this registration to the cart
    // Remove any other main events that might be in cart
    $cart = new \EventbookingHelperCart();

    $items = $cart->getItems();
    if (!in_array($this->targetPackage->eventId, $items)) {
      $cart->reset();
      array_unshift($items, $this->targetPackage->eventId);
      $cart->addEvents($items);
      $items = $cart->getItems();
    }

    // In case there are any oddball nulls, clean them out
    $cart->remove(null);

    $cartMainEvents = array_intersect($items, $this->eventConfig->getMainEventIds());

    if (sizeof($cartMainEvents) > 1) {
      foreach ($cartMainEvents as $c) {
        if ($c != $this->targetPackage->eventId) {
          $cart->remove($c);
        }
      }
    }
  }

  private function resetSession()
  {
    // Cache date limits for this event to filter in .../model/list.php
    Helpers::sessionSet('filter_start', $this->eventConfig->eventInfo->start_date->toSql());
    Helpers::sessionSet('filter_end', $this->eventConfig->eventInfo->end_date->toSql());
    Helpers::sessionSet('filter_duration', '');

    Helpers::sessionSet('eventAlias', $this->eventAlias);
    Helpers::sessionSet('eventAction', $this->action);
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

    $this->handleMetaPackages();

    $this->setLayout($this->eventAlias);
    parent::display();
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

  /**
   * Autopopulate cart if the selected package is a "meta" package,
   * meaning, the event's meta column includes other event ids.
   * This applies only to registration (there are combo meal packages
   * that have meta data handled during the checkin process)
   * Redirects the user to the Event Booking cart.
   */
  private function handleMetaPackages()
  {
    // See: administrator/components/com_claw/forms/packageinfo.xml
    //showon="packageInfoType:3[OR]eventPackageType:3[OR]eventPackageType:32[OR]eventPackageType:20"
    $metaPackages = [
      EventPackageTypes::vip,
      EventPackageTypes::claw_staff,
      EventPackageTypes::claw_board,
    ];

    if (!in_array($this->eventPackageType, $metaPackages)) return;

    $cart = new \EventbookingHelperCart();
    $cart->reset();

    $cartEventIds = [$this->targetPackage->eventId];

    foreach ($this->targetPackage->meta as $packageInfo) {
      $cartEventIds[] = $packageInfo;
    }

    $cart->addEvents($cartEventIds);

    // In case they want to come back, fall back to vip
    Helpers::sessionSet('eventAction', $this->eventPackageType->value);

    $this->app->redirect('/index.php?option=com_eventbooking&view=cart');
    return;
  }
}
