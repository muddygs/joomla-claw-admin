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
use Joomla\CMS\User\User;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Application\SiteApplication;

use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Lib\EventConfig;
use ClawCorpLib\Enums\EventPackageTypes;
use ClawCorpLib\Enums\PackageInfoTypes;
use ClawCorpLib\Enums\EbPublishedState;
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
  private User $identity;
  public string $eventAlias;
  public string $action;
  public EventConfig $eventConfig;
  public string $registrationSurveyLink;
  public ?EventPackageTypes $eventPackageType;
  public ?PackageInfo $targetPackage;
  public ?RegistrantRecord $mainEvent;
  public bool $addons = false;
  public string $eventDescription;
  public array $mealCategoryIds = [];
  public string $shiftsBaseUrl;
  public string $coupon = '';
  public string $tab = 'Meals';

  public function __construct($config = [])
  {
    /** @var \Joomla\CMS\Application\SiteApplication */
    $this->app = Factory::getApplication();

    parent::__construct($config);

    $input = $this->app->getInput();
    $this->eventAlias = $input->get('event', '');
    $this->action = $input->get('action', 0);

    $this->eventPackageType = EventPackageTypes::tryFrom($this->action);

    if (is_null($this->eventPackageType) || $this->eventPackageType == EventPackageTypes::none) {
      $this->app->enqueueMessage('Invalid registration action requested.', 'error');
      $this->app->redirect($this->registrationSurveyLink);
      return;
    }

    $activeEvents = EventConfig::getActiveEventAliases(mainOnly: true);
    if (!in_array($this->eventAlias, $activeEvents)) {
      $this->eventAlias = Aliases::current(true);
    }
    $this->eventConfig = new EventConfig($this->eventAlias);
    $this->targetPackage = $this->eventConfig->getPackageInfo($this->eventPackageType);

    //
    // Redirect to public events (no authentication required)
    //
    $public_acl = Config::getGlobalConfig('packageinfo_public_acl', 0);
    if ($this->targetPackage->acl_id == $public_acl) {
      if (
        $this->targetPackage->published != EbPublishedState::published
        || $this->targetPackage->eventId == 0
      ) {
        parent::display('blocked');
        return;
      }

      // Clear cart prior to individual event registration
      $cart = new \EventbookingHelperCart();
      $cart->reset();

      $url    = 'index.php?option=com_eventbooking&view=register&event_id=' . $this->targetPackage->eventId;
      $this->app->redirect($url);
      return true;
    }

    $this->identity = $this->app->getIdentity();

    if (!$this->isAuthenticated()) die('Redirect error in Registration Options');


    $this->resetSession();
    $this->registrationSurveyLink = Helpers::sessionGet('registrationSurveyLink', '/');


    $registrant = new Registrant($this->eventAlias, $this->identity->id);
    $this->mainEvent = $registrant->getMainEvent();

    if (!$this->isValidTargetPackage()) {
      $this->app->enqueueMessage('Invalid package registration requested.', 'error');
      $this->app->redirect($this->registrationSurveyLink);
      return;
    }

    if (!$this->isAuthorized()) die('Redirect error in Registration Options [2]');

    if (!$this->addons) $this->setVolunteerDefaultTab();

    $this->resetCart();
    $this->eventDescription = !$this->addons ? $this->targetPackage->title . ' Registration' : $this->mainEvent->event->title . ' Addons';
    $this->mealCategoryIds = $this->getMealCategoryIds();

    $config = new Config($this->eventAlias);
    $this->shiftsBaseUrl = $config->getConfigText(ConfigFieldNames::CONFIG_URLPREFIX, 'shifts');

    $this->coupon = Helpers::sessionGet('clawcoupon');
  }

  private function setVolunteerDefaultTab()
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

  private function getMealCategoryIds(): array
  {
    $categoryIds = [];

    $keys = [
      'eb_cat_dinners',
      'eb_cat_brunches',
      'eb_cat_buffets',
    ];

    foreach ($keys as $key) {
      if ($this->eventConfig->eventInfo->$key > 0) {
        $categoryIds[] = $this->eventConfig->eventInfo->$key;
      }
    }

    if (! $this->eventConfig->eventInfo->onsiteActive && $this->eventConfig->eventInfo->eb_cat_combomeals > 0) {
      $categories[] = $this->eventConfig->eventInfo->eb_cat_combomeals;
    }

    return $categoryIds;
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

  private function isValidTargetPackage(): bool
  {
    if ($this->eventPackageType == EventPackageTypes::addons && !is_null($this->mainEvent)) {
      $this->addons = true;
      return true;
    }

    $valid = false;
    /** @var \ClawCorpLib\Lib\PackageInfo */
    foreach ($this->eventConfig->packageInfos as $packageInfo) {
      if (
        $packageInfo->eventPackageType == $this->targetPackage->eventPackageType &&
        $packageInfo->published == EbPublishedState::published &&
        $packageInfo->packageInfoType == PackageInfoTypes::main
      ) {
        $valid = true;
        break;
      }
    }

    $valid = $valid || $this->targetPackage->eventPackageType == EventPackageTypes::addons;
    return $valid;
  }

  private function isAuthenticated(): bool
  {
    // Redirect to login page
    if (!$this->identity->id) {
      $return = \Joomla\CMS\Uri\Uri::getInstance()->toString();
      $url    = 'index.php?option=com_users&view=login';
      $url   .= '&return=' . base64_encode($return);
      $this->app->enqueueMessage('Please sign in to continue registration.', 'warning');
      $this->app->redirect($url);
      return false;
    }

    return true;
  }

  private function isAuthorized(): bool
  {
    # addons are inherently assigned "registered" ACL, so we can (currently)
    # be assured we're good to continue
    if ($this->addons) return true;

    $acl = $this->identity->getAuthorisedViewLevels();
    if (! in_array($this->targetPackage->acl_id, $acl)) {
      $this->app->enqueueMessage('You are not authorized to register for this event.', 'error');
      $this->app->redirect($this->registrationSurveyLink);
      return false;
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

    if (!in_array($this->eventPackageType, $metaPackages)) {
      Helpers::sessionSet('autocart', '0');
      return;
    }

    $autocart = Helpers::sessionGet('autocart', '0');

    if (0 != $autocart) return;

    $cart = new \EventbookingHelperCart();
    $cart->reset();

    $cartEventIds = [$this->targetPackage->eventId];

    foreach ($this->targetPackage->meta as $packageInfo) {
      $cartEventIds[] = $packageInfo;
    }

    $cart->addEvents($cartEventIds);

    // In case they want to come back
    Helpers::sessionSet('eventAction', $this->eventPackageType->value);
    Helpers::sessionSet('autocart', '1');

    $this->app->redirect('/index.php?option=com_eventbooking&view=cart');
    return;
  }
}
