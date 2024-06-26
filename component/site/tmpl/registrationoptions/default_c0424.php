<?php
defined('_JEXEC') or die;

use ClawCorpLib\Enums\ConfigFieldNames;
use ClawCorpLib\Enums\EventPackageTypes;
use Joomla\CMS\Factory;

require_once(JPATH_ROOT . '/components/com_eventbooking/helper/cart.php');
require_once(JPATH_ROOT . '/components/com_eventbooking/helper/database.php');
require_once(JPATH_ROOT . '/components/com_eventbooking/helper/helper.php');

use ClawCorpLib\Helpers\Bootstrap;
use ClawCorpLib\Helpers\Config;
use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Lib\ClawEvents;
use ClawCorpLib\Lib\EventInfo;
use ClawCorpLib\Lib\Registrant;
use Joomla\CMS\HTML\HTMLHelper;

/** @var \ClawCorp\Component\Claw\Site\View\Registrationoptions\HtmlView $this */

$eventPackageType = EventPackageTypes::none;

Helpers::sessionSet('filter_duration','');

$tab = 'Meals';
$addons = false;
$vipRedirect = false;

// NEXT EVENT: change to use url param to switch case, e.g., /claw22?e=reg-att 
// OR /reg?e=nnn where nnn = $EventPackageTypes::xxx
// ALSO: is it possible to do /claw22/reg-att ? Maybe with URL rewrite in .htaccess?

switch ($this->action) {
  case EventPackageTypes::attendee->value:
    $eventPackageType = EventPackageTypes::attendee;
    break;
  case EventPackageTypes::vip->value:
      $eventPackageType = EventPackageTypes::vip;
    break;
  case EventPackageTypes::vip2->value:
    $eventPackageType = EventPackageTypes::vip;
    $vipRedirect = true;
    break;
  case EventPackageTypes::volunteer2->value:
    $eventPackageType = EventPackageTypes::volunteer2;
    $tab = 'Shifts';
    break;
  case EventPackageTypes::volunteer3->value:
    $eventPackageType = EventPackageTypes::volunteer3;
    $tab = 'Shifts';
    break;
  case EventPackageTypes::volunteersuper->value:
    $eventPackageType = EventPackageTypes::volunteersuper;
    $tab = 'Shifts';
    break;
  case EventPackageTypes::event_staff->value:
    $eventPackageType = EventPackageTypes::event_staff;
    break;
  case EventPackageTypes::event_talent->value:
    $eventPackageType = EventPackageTypes::event_talent;
    break;
  case EventPackageTypes::claw_staff->value:
    $eventPackageType = EventPackageTypes::claw_staff;
    break;
  case EventPackageTypes::vendor_crew->value:
    $eventPackageType = EventPackageTypes::vendor_crew;
    break;
  case EventPackageTypes::educator->value:
    $eventPackageType = EventPackageTypes::educator;
    break;
  case EventPackageTypes::addons->value:
    $eventPackageType = EventPackageTypes::none;
    $addons = true;
    break;
  case EventPackageTypes::day_pass_fri->value:
    $eventPackageType = EventPackageTypes::day_pass_fri;
    break;
  case EventPackageTypes::day_pass_sat->value:
    $eventPackageType = EventPackageTypes::day_pass_sat;
    break;
  case EventPackageTypes::day_pass_sun->value:
    $eventPackageType = EventPackageTypes::day_pass_sun;
    break;
  default:
    echo 'Unknown action. Please try again.';
    return;
}

?>
<h1>Registration Options for <?= $this->eventConfig->eventInfo->description ?></h1>
<?php

if ( !$this->eventConfig->eventInfo->onsiteActive ) {
  $blockedPackageTypes = [
    EventPackageTypes::day_pass_fri,
    EventPackageTypes::day_pass_sat,
    EventPackageTypes::day_pass_sun,
  ];

  if (in_array($eventPackageType, $blockedPackageTypes)) {
    echo "Day passes are not available at this time.";
    return;
  }
}

$uid = Factory::getApplication()->getIdentity()->id;

// Redirect to login page
if (!$uid) {
  $return = \Joomla\CMS\Uri\Uri::getInstance()->toString();
  $url    = 'index.php?option=com_users&view=login';
  $url   .= '&return=' . base64_encode($return);
  $this->app->enqueueMessage('Please sign in to continue registration.', 'warning');
  $this->app->redirect($url);
}

$registrant = new registrant($this->eventAlias, $uid);

/** @var ClawCorpLib\Lib\Registrant\RegistrantRecord */
$mainEvent = $registrant->getMainEvent();

if ($addons == true && $mainEvent == null && !$vipRedirect) :
?>
  <p class="text-warning">You are not currently registered. Please start your registration <a href="/registration-survey-claw">here</a>.</p>
  <p><span class="fa fa-info-circle fa-2x"></span><a href="/help?category_id=11">&nbsp;Contact Guest Services for assistance.</a></p>
<?php
  return;
endif;

if ($addons == false && $mainEvent != null && $mainEvent->registrant->eventPackageType != $eventPackageType) :
?>
  <p class="text-warning">You cannot register for this event because you are already registered for <b><?= $mainEvent->event->title ?></b>.</p>
  <p><span class="fa fa-info-circle fa-2x"></span><a href="/help?category_id=11">&nbsp;Contact Guest Services for assistance.</a></p>
<?php
  return;
endif;

#region Toast
/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->app->getDocument()->getWebAssetManager();
$wa->useScript('com_claw.toast');
// TODO: Load properly
?>

<div class="position-fixed top-50 start-50 translate-middle p-3" style="z-index: 11">
  <div id="liveToast" class="toast rounded-pill" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="toast-header rounded-pill">
      <p style="line-height:1rem;" class="me-auto small m-1">Event added to cart.<br>Click Cart Button (above) to check out.</p>
      <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
</div>
<?php
#endregion Toast

//$clawEvents = $this->events;
$eventInfo = $this->eventConfig->eventInfo;
$clawEvents = null;

// Cache date limits for this event to filter in .../model/list.php
Helpers::sessionSet('filter_start' , $eventInfo->start_date->toSql());
Helpers::sessionSet('filter_end' , $eventInfo->end_date->toSql());

/** @var \ClawCorpLib\Lib\PackageInfo */
$regEvent = $this->eventConfig->getPackageInfo($eventPackageType);

// Auto add this registration to the cart
// Remove any other main events that might be in cart
if ($addons == false && $mainEvent == null) {
  $cart = new EventbookingHelperCart();

  $items = $cart->getItems();
  if (!in_array($regEvent->eventId, $items)) {
    $cart->reset();
    array_unshift($items, $regEvent->eventId);
    $cart->addEvents($items);
    $items = $cart->getItems();
  }

  // In case there are any oddball nulls, clean them out
  $cart->remove(null);

  $cartMainEvents = array_intersect($items, $this->eventConfig->getMainEventIds());

  if (sizeof($cartMainEvents) > 1) {
    foreach ( $cartMainEvents AS $c )
    {
      if ( $c != $regEvent->eventId )
      {
        $cart->remove($c);
      }
    }
  }
}

if ( $vipRedirect ) {
  $cart = new \EventbookingHelperCart();
  $cart->reset();

  /** @var \ClawCorpLib\Lib\PackageInfo */
  $vipClawEvent = $this->eventConfig->getPackageInfo(EventPackageTypes::vip);
  $comboMealsAll = $vipClawEvent->meta[0];
  $cart->addEvents([$vipClawEvent->eventId, $comboMealsAll]);

  // In case they want to come back, fall back to vip
  Helpers::sessionSet('eventAction', EventPackageTypes::vip->value);

  $this->app->redirect('/index.php?option=com_eventbooking&view=cart');
}

$coupon = Helpers::sessionGet('clawcoupon');

$couponHtml = '';
if ($coupon != '') {
  $couponHtml = <<<HTML
  <p style="margin-bottom:0px !important;">Your Coupon Code: <strong>$coupon</strong></p>
HTML;
}

$eventDescription = $addons == false ? $regEvent->title . ' Registration' : $mainEvent->event->title. ' Addons';
$optionslink = Helpers::sessionGet('optionslink','/');

?>
  <div class="container">
    <div class="row">
      <div class="col-6 col-lg-2">
        <div class="d-grid"> 
          <a href="<?= $optionslink ?>" class="btn btn-danger" role="button"><i class="fa fa-chevron-left"></i> Back</a>
        </div>
      </div>
      <div class="col-6 col-lg-6">
        <h1><?php echo $eventDescription ?></h1>
        <?php echo $couponHtml ?>
      </div>
      <div class="col-12 col-lg-4">
        <div class="d-grid gap-2">
          <a href="/index.php?option=com_eventbooking&view=cart" role="button" class="btn btn-warning btn-lg">
          <span class="fa fa-shopping-cart" aria-hidden="true"></span>&nbsp;Review Cart and Checkout
          </a>
        </div>
      </div>
    </div>
  </div>

<?php

if ($mainEvent != null ) :
?>
  <p class="text-warning">
    <b>You are already registered. To view all your registrations, click <a href="/planning/my-reg">here</a></b>.
  </p>
<?php
endif;
  ?>

  <div class="border border-info rounded p-3 mb-3 mt-3 text-center">
    <span style="font-size:large;">Click on the tab buttons below for more add ons.</span>
  </div>
  <?php

  // Define tab headings
  $content = [];
  $headings = [];

  $headings[] = 'Shifts';
  if ( $this->eventConfig->eventInfo->onsiteActive ) {
    $content[] = contentShiftsOnsite();
  } else {
    $content[] = contentShifts($this->eventConfig->eventInfo, $eventPackageType);
  }
  
  $headings[] = 'Meals';
  $content[] = contentMeals($this->eventConfig->eventInfo);

  $headings[] = 'Speed Dating';
  $content[] = contentSpeedDating($this->eventConfig->eventInfo);

  if ( !$this->eventConfig->eventInfo->onsiteActive ) {
    $headings[] = 'Rentals';
    $content[] = contentRentals($this->eventConfig->eventInfo);
  }

  $headings[] = 'Community';
  $content[] = contentLeatherHeart($this->eventConfig->eventInfo);

  Bootstrap::writePillTabs($headings, $content, $tab);

  echo contentSponsorships();

  // end output

  function categoryLinkButtons(string $urlPrefix, array $categoryIds): string
  {
      $categoryInfo = ClawEvents::getRawCategories($categoryIds);
      $html = [];
  
      foreach ($categoryInfo as $alias => $info) {
          $url = $urlPrefix . $alias;
          $html[] = <<<HTML
              <div class="col d-flex flex-wrap">
                  <a href="$url" class="w-100 btn btn-outline-danger" role="button">
                      <h2>{$info->name}</h2>
                      <small class="text-center" style="color:#ffae00">{$info->meta_description}</small>
                  </a>
              </div>
          HTML;
      }
  
      return '<div class="row row-cols-1 row-cols-sm-2 g-2 px-4 py-2">' . implode('', $html) . '</div><div class="clearfix"></div>';
  }
  

#region content

  function contentShiftsOnsite(): string
  {
    $result = <<<HTML
    <div class="border border=info text-white p-3 mx-2 mb-2 rounded">
    <span style="font-size:large;">
      <i class="fa fa-info-circle fa-2x"></i>&nbsp;After you register, 
      please go to the Volunteer Assignments Desk to get your shift assignments.
    </span>
    <br>Remember:
    <ul class="mt-2">
      <li>You must show up to your shift <u>15 minutes early</u></li>
      <li>Allow time between shifts for break and travel</li>
      <li>CLAW reserves the right to change your shifts (with sufficient notification)</li>
    </ul>
  </div>
HTML;

    return $result;
  }

  function contentShifts( EventInfo $eventInfo, EventPackageTypes $EventPackageType ): string
  {
    $result = <<<HTML
  <div class="border border=info text-white p-3 mx-2 mb-2 rounded">
  <span style="font-size:large;"><i class="fa fa-info-circle fa-2x"></i>&nbsp;Select shifts from <u>one category</u>, then times that work for you. Please note the requirements listed for each shift.</span><br>Remember:
  <ul class="mt-2">
    <li>You must show up to your shift <u>15 minutes early</u></li>
    <li>Allow time between shifts for break and travel</li>
    <li>CLAW reserves the right to change your shifts (with sufficient notification)</li>
  </ul>
</div>
HTML;

    $categoryIds = $eventInfo->eb_cat_shifts;
    if ( $EventPackageType == EventPackageTypes::volunteersuper ) {
      $categoryIds = array_merge($categoryIds, $eventInfo->eb_cat_supershifts);
    }

    $config = new Config($eventInfo->alias);
    $baseURL = $config->getConfigText(ConfigFieldNames::CONFIG_URLPREFIX, 'shifts');

    if ( null == $baseURL ) {
      die('shift base URL not found');
    }

    $result .= categoryLinkButtons($baseURL, $categoryIds);

    return $result;
  }

  function contentMeals(EventInfo $eventInfo): string
  {
    $result = '';

    $categories = [
      $eventInfo->eb_cat_dinners,
      $eventInfo->eb_cat_brunches,
      $eventInfo->eb_cat_buffets,
    ];

    if ( ! $eventInfo->onsiteActive ) {
      $categories[] = $eventInfo->eb_cat_combomeals;
    }

    $categoryInfo = ClawEvents::getRawCategories($categories);
    
    foreach ($categoryInfo as $info) {
      $content = "{ebcategory {$info->id} toast}";
      $result .= HTMLHelper::_('content.prepare', $content);
    }

    return $result;
  }

  function contentSpeedDating(EventInfo $eventInfo): string
  {
    $categoryIds = $eventInfo->eb_cat_speeddating;
    $content = '{ebcategory ' . $categoryIds[0] . ' toast}';
    return HTMLHelper::_('content.prepare', $content);
  }

  function contentRentals(EventInfo $eventInfo): string
  {
    $categoryIds = $eventInfo->eb_cat_equipment;
    $content = '{ebcategory ' . $categoryIds[0] . ' toast}';
    return HTMLHelper::_('content.prepare', $content);
  }

  function contentSponsorships(): string
  {
    return <<<HTML
<div class="border rounded-top border-primary p-3 mt-3 mb-2 text-center">
  <b>Sponsor a CLAW Event!</b> Options available from single events to Master Sponsorships. 
  Click <a href="/sponsor/sponsor-events" target="_blank">HERE</a> for more details.</div>
HTML;
  }

  function contentLeatherHeart(EventInfo $eventInfo): string
  {
    $result = <<<HTML
  <div class="border border=info text-white p-3 mx-2 mb-2 rounded">
    <span style="font-size:large;"><i class="fa fa-heart fa-2x"></i>&nbsp;Leather Heart Events:
    Help CLAW volunteers or a community member.</span>
  </div>
HTML;

    $result = ''; // for now

    $categoryIds = $eventInfo->eb_cat_sponsorship;
    $content = '{ebcategory ' . $categoryIds[0] . ' toast}';
    $result .= HTMLHelper::_('content.prepare', $content);

    return $result;
  }

#endregion