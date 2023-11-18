<?php
defined('_JEXEC') or die;

use ClawCorpLib\Enums\EventPackageTypes;
use Joomla\CMS\Factory;

require_once(JPATH_ROOT . '/components/com_eventbooking/helper/cart.php');
require_once(JPATH_ROOT . '/components/com_eventbooking/helper/database.php');
require_once(JPATH_ROOT . '/components/com_eventbooking/helper/helper.php');

use ClawCorpLib\Helpers\Bootstrap;
use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Lib\Aliases;
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

if ( !$this->eventInfo->onsiteActive ) {
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

if (!$uid) {
  if ( $this->eventInfo->onsiteActive ) {
    // Redirect to login page
    $return = \Joomla\CMS\Uri\Uri::getInstance()->toString();
    $url    = 'index.php?option=com_users&view=login';
    $url   .= '&return=' . base64_encode($return);
    $this->app->redirect($url);
  }
  
  echo 'You must be signed in to see this resource';
  return;
}

$registrant = new registrant($this->eventAlias, $uid);

/** @var ClawCorpLib\Lib\Registrant\RegistrantRecord */
$mainEvent = $registrant->getMainEvent();

if ($addons == true && $mainEvent == null && !$vipRedirect) :
?>
  <p class="text-warning">You are not currently registered. Please start your registration <a href="/registration-survey">here</a>.</p>
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
      <strong style="line-height:1rem;" class="me-auto small m-1">Event added to cart.<br>Click Cart Button (above) to check out.</strong>
      <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
</div>
<?php
#endregion Toast

$clawEvents = $this->events;
$eventInfo = $this->eventInfo;

// Cache date limits for this event to filter in .../model/list.php
Helpers::sessionSet('filter_start' , $eventInfo->start_date);
Helpers::sessionSet('filter_end' , $eventInfo->end_date);

$regEvent = $clawEvents->getEventByKey('eventPackageType', $eventPackageType);

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

  $cartMainEvents = array_intersect($items, $clawEvents->mainEventIds);

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

  $vipClawEvent = $this->events->getEvent()->getClawEvent(EventPackageTypes::vip);
  $comboMealsAll = $vipClawEvent->meta[0];
  $vip = $this->events->getEvent()->getEventId(EventPackageTypes::vip);
  $cart->addEvents([$vip, $comboMealsAll]);

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

$eventDescription = $addons == false ? $regEvent->description . ' Registration' : $mainEvent->event->title. ' Addons';
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
  if ( $this->eventInfo->onsiteActive ) {
    $content[] = contentShiftsOnsite();
  } else {
    $content[] = contentShifts($eventPackageType);
  }
  
  $headings[] = 'Meals';
  $content[] = contentMeals($this->eventInfo);

  $headings[] = 'Speed Dating';
  $content[] = contentSpeedDating();

  if ( !$this->eventInfo->onsiteActive ) {
    $headings[] = 'Rentals';
    $content[] = contentRentals();
  }

  $headings[] = 'Community';
  $content[] = contentLeatherHeart();

  // $content[] = contentParties();
  Bootstrap::writePillTabs($headings, $content, $tab);

  echo contentSponsorships();

  // end output

  function categoryLinkButtons(array $categoryAliases, string $urlPrefix): string
  {
    $categoryInfo = ClawEvents::getCategoryNames($categoryAliases);

    $result = '<div class="row row-cols-1 row-cols-sm-2 g-2 px-4 py-2">';

    foreach ($categoryAliases as $alias) {
      $url = $urlPrefix . $alias;
      $result .= '<div class="col d-flex flex-wrap">';
      $result .= '<a href="' . $url . '" class="w-100 btn btn-outline-danger" role="button"><h2>' . $categoryInfo[$alias]->name . '</h2><small class="text-center" style="color:#ffae00">'.$categoryInfo[$alias]->meta_description.'</small></a>';
      $result .= '</div>';
    }
    $result .= '</div>';
    $result .= '<div class="clearfix"></div>';

    return $result;
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

  function contentShifts( EventPackageTypes $EventPackageType ): string
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

    $c = Aliases::shiftCategories();

    if ( $EventPackageType != EventPackageTypes::volunteersuper )
    {
      $c = array_diff($c, ['shifts-float']);
    }


    $result .= categoryLinkButtons($c, '/claw-all-events/shifts/');

    return $result;
  }

  function contentMeals(EventInfo &$eventInfo): string
  {
    // TODO: temporary use of C24 category for meals
    $result = '';
    $categoryIds = ClawEvents::getCategoryIds([
      $eventInfo->prefix == 'C24' ? 'dinner-cle' : 'dinner',
      'buffet',
      'buffet-breakfast',
    ]);

    if ( !$eventInfo->onsiteActive ) {
      $categoryIds[] = ClawEvents::getCategoryId($eventInfo->prefix == 'C24' ? 'meal-combos-cle' : 'meal-combos');
    }

    foreach ($categoryIds as $id) {
      $content = "{ebcategory $id toast}";
      $prepared = HTMLHelper::_('content.prepare', $content);
      $result .= $prepared;
    }

    return $result;
  }

  function contentSpeedDating(): string
  {
    $categoryIds = ClawEvents::getCategoryIds(['speed-dating']);
    $content = '{ebcategory ' . $categoryIds[0] . ' toast}';
    return HTMLHelper::_('content.prepare', $content);
  }

  function contentRentals(): string
  {
    $categoryIds = ClawEvents::getCategoryIds(['equipment-rentals']);
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

  function contentLeatherHeart(): string
  {
    $result = <<<HTML
  <div class="border border=info text-white p-3 mx-2 mb-2 rounded">
    <span style="font-size:large;"><i class="fa fa-heart fa-2x"></i>&nbsp;Leather Heart Events:
    Help CLAW volunteers or a community member.</span>
  </div>
HTML;

    $result = ''; // for now

    $categoryIds = ClawEvents::getCategoryIds(['donations-leather-heart']);
    $content = '{ebcategory ' . $categoryIds[0] . ' toast}';
    $result .= HTMLHelper::_('content.prepare', $content);

    return $result;
  }

#endregion