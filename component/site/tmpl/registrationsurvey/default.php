<?php

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use ClawCorpLib\Helpers\Bootstrap;
use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Lib\ClawEvents;
use ClawCorpLib\Lib\Registrant;
use ClawCorpLib\Lib\Coupons;

Helpers::sessionSet('clawcoupon','');
Helpers::sessionSet('clawcouponrequest','');

$app = Factory::getApplication();
/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->useScript('com_claw.registrationsurvey');

$coupon = trim($app->input->get('coupon', '', 'string'));
$mainEvent = null;
$uid = 0;

$couponInfo = null;

if ( !Aliases::onsiteActive )
{
  // Let's see if already registered
  $uid = Factory::getUser()->id;

  if (0 == $uid) {
    $return = \Joomla\CMS\Uri\Uri::getInstance()->toString();
    $url    = 'index.php?option=com_users&view=login';
    $url   .= '&return='.base64_encode($return);
    $app->redirect($url);  
  }

  $registrant = new Registrant(Aliases::current, $uid);
  $registrant->loadCurrentEvents();
  $mainEvent = $registrant->getMainEvent();

  // Has a coupon already been generated for this registrant?

  if ( '' == $coupon && null == $mainEvent  )
  {
    $events = new ClawEvents('c0423');
    $eventIds = $events->getEventIds();
    $couponInfo = Coupons::getAssignedCoupon($uid, $eventIds);

    if ( $couponInfo != null ) $coupon = $couponInfo->code;
  }
}
else
{
  if ( Factory::getUser()->id != 0 ) $app->logout();
}

?>
<script src="/js/registrationsurvey_events.js"></script>
<div style="text-align: center;">
  <p><img alt="Registration Banner" src="images/<?=Aliases::defaultPrefix?>/banners/Registration.png" class="img-fluid mx-auto d-block" /></p>
</div>

<?php
$ref = Helpers::sessionGet('referrer');
if ( $ref == 'CARAS' ):?>
<img alt="CARAS Banner" title="CARAS Banner" src="https://www.clawinfo.org/images/L22/adsliders/2/L22_CLAWbannerAd_cigar.png" class="img-fluid mx-auto mb-3" />
<?php endif;
?>

<div class="border border-2 border-info rounded mb-5">
<h3 class="m-2 text-center">One Registration Per Person. Any addons must be purchased <u>per registration</u>.</h3>
</div>

<?php
if ( $mainEvent != null ):
?>
<h1>You are already registered</h1>
<div class="d-grid gap-2 col-6 mx-auto mb-3">
    <a href="/planning/my-reg" role="button" class="btn btn-danger">View Registrations</a>
    <a href="/<?=Aliases::defaultPrefix?>-reg-addons" role="button" class="btn btn-success">Get Addons</a>
</div>
<p>If you are trying to register another person, please SIGN OUT (under the Registration menu) and start again using that person's account.</p>
<?php
$groups = getUserGroupsByName();
if (!array_key_exists('Super Users', $groups)) {
  return;
}
endif;

if ( !Aliases::onsiteActive ) {
  # no actions here yet
} else {
  ?>
    <h1>Already Registered?</h1>
    <div class="d-grid mb-3">
    <a href="/<?=Aliases::defaultPrefix ?>-reg-addons" class="btn btn-success btn-lg" role="button">
      Click Here To Get Add Ons
    </a>
    </div>
  <?php
}

?>
<form action="/php/pages/registrationsurvey.php" method="post" name="Coupon Validator" id="registration-survey-coupon" class="row">
<?php

if ( null == $couponInfo ):
?>
  <h1>Have a coupon?</h1>
    <?php
else:
      $databaseRow = clawEvents::loadEventRow($couponInfo->event_id);
    ?>
      <h1>You have a coupon assigned to your account</h1>
      <p>Coupon Event Assignment: <strong><?=$databaseRow->title ?></strong></p>  
      <ul>
        <li>To request a different coupon type, contact <a href="/help?category_id=17">Guest Services</a>.</li>
        <li>If you have a different coupon, please enter it below.</li>
        <li>If you do not wish to use this coupon, please select a registration type below.</li>
      </ul>
    <?php
endif;
    ?>
  <label for="coupon" class="form-label">Enter your coupon below and click the START REGISTRATION button. Coupons are not
    case sensitive.
  </label>
  <div class="input-group mb-3">
    <input type="text" class="form-control" name="coupon" value="<?=$coupon ?>" id="coupon" placeholder="?-ABCD-EFGH" aria-label="Coupon Entry FIeld">
    <button class="btn btn-danger" type="button" onclick="validateCoupon()">START REGISTRATION</button>
  </div>
  <div id="couponerror" class="bg-danger text-light rounded-2 d-none">
    That coupon is not valid. Please verify your entry.
<?php
  if ( Aliases::onsiteActive ):
?>
    Go to the registration help desk for assistance.
<?php
  else:
?>
    For assistance, contact <a href="/help?category_id=17">Guest Services</a>.
<?php
  endif;
?>
  </div>
</form>

<h1>Or Select Registration Type</h1>

<?php
$tabs = ['Attendee','Volunteer','VIP'];
$html = [];
$html[] = attendeeHtml();
$html[] = volunteerHtml($uid);
$html[] = vipHtml();

if ( Aliases::onsiteActive ) {
  $tabs[] = 'Day Passes';
  $html[] = dayPassesHtml();
  $tabs[] = 'Passes';
  $html[] = nightPassesHtml();
}
else
{
  $tabs[] = 'Other';
  $html[] = otherHtml();
}

Bootstrap::writePillTabs($tabs, $html, 'none');

function attendeeHtml(): string
{
  $link = '/'.Aliases::defaultPrefix.'-reg-att';

  if ( Aliases::onsiteActive ) {
    return <<<HTML
  <div class="container">
    <div class="d-grid">
    <a href="$link" class="btn btn-danger btn-lg" role="button">
          Click To Register As An Attendee ($199 early/$249 standard/$299 onsite)
      </a>
    </div>
  </div>
HTML;
  }

  $content = [
    'ticket-alt' => ['Attendee Registration','For standard registration (not Volunteer, Educator, Recruited Volunteer, or VendorMart Crew)<br><a href="'.$link.'" role="button" class="btn btn-danger">Start Registration</a>'],
    'user-tag' => ['CLAW Nation','Please enter your CLAW Nation coupon code above and click Start Registration or contact <a href="/planning/guest-services?category_id=10">guest services</a> to obtain your registration coupon - CLAW NATION ONLY.'],
  ];
  
  $tags = [
    ['<h4 class="fw-bold mb-0">','</h4>'],
    ['<p>','</p>']
  ];

  $result = Bootstrap::writeGrid($content, $tags, true);
  return $result;

}

function volunteerHtml(int $uid = 0): string
{
  $link2 = '/'.Aliases::defaultPrefix.'-reg-vol2';
  $link3 = '/'.Aliases::defaultPrefix.'-reg-vol3';

  if ( Aliases::onsiteActive ) {
    return <<<HTML
  <div class="container">
    <ul>
      <li>New for Leather Getaway 23: Simplified Registration - No More Coupons</li>
      <li>Register at a reduced rate. If you do not perform your shifts, we will charge the card on file up to
        the cost of an Attendee Registration package rate.</li>
      <li>Volunteers choosing to do three shifts - check out for $1.</li>
      <li>Volunteers choosing to do two shifts - check out for $99.</li>
      <li>All on-site volunteer shifts are assigned at the Volunteer Assignments Desk</li>
  </ul>
    <div class="d-grid gap-2">
    <a href="$link2" class="btn btn-danger btn-lg" role="button">
          Click To Register As A Volunteer (2 Shifts)
    </a>
    <a href="$link3" class="btn btn-danger btn-lg" role="button">
          Click To Register As A Volunteer (3 Shifts)
    </a>
    </div>
  </div>
HTML;
  }

  $result = <<<HTML
<h2>Volunteer Registration Information</h2>
<p>We are proud that nearly half of the people at CLAW are volunteers. With more than 150 events and exhibitors
in 4 days, the volunteers are an essential element that makes CLAW possible.</p>
<p><span class="badge rounded-pill bg-danger">New</span>Volunteer Packages no longer require deposit. Standard volunteer
options are:
<ul>
  <li>Volunteer for 3-shifts for a $1 package fee</li>
  <li>Volunteer for 2-shifts for a $99 package fee</li>
</ul>
<p>If you are interested in a leadership or recruited volunteer position, please complete the survey 
<a href="https://forms.gle/qoJ61i9qQZ1bCvLv5" target="_blank">here.</a> You may also find more information about leadership 
opportunities <a href="https://clawinfo.org/planning/leadership-opportunities" target="_blank">here.</a></p>

<ul>
  
  <li>Volunteer shifts are 4-5 hours each</li>
  <li>Volunteer Packages include all the benefits of the attendee package, plus all of the following:</li>
  <ul>
    <li><span style="color:#ffae00">Food and Drinks:</span> Free food and non-alcoholic drinks in the Volunteer Hospitality Suite at the host hotel all weekend long</li>
    <li><span style="color:#ffae00">Discounts</span> on event meals (calculated at checkout)</li>
    <li><span style="color:#ffae00">Automatic Entry</span> into the Volunteer Raffle to win a $1,500 prize for CLAW 24 or Leather Getaway 24, including 4 nights at a CLAW hotel</li>
  </ul>
  <li>There are many volunteers that should register as a Recruited Volunteer, such as supervisors, bootblacks, entertainers, photographers, and cashiers. If you are interested 
  in one of these positions, please complete the survey <a href="https://forms.gle/qoJ61i9qQZ1bCvLv5" target="_blank">here</a> and do not register at this time. Guest Services will contact you to discuss opportunities.</li>
  <li>All volunteers pay a reduced-rate registration fee. This puts your credit card on file.</li> 
  <li>Volunteers are vital to the success of the event. Signing up to volunteer is a promise that you will attend all shifts on time and ready to work. Failure to attend some or all of your volunteer shifts will result in charges to your credit card, up to the cost of a full attendee package ($249). 
  Volunteers with complimentary or reduced cost accommodations (including recruited and super volunteers) who do not attend shifts may also be charged for the cost of their accommodations.</li>
  <li>The volunteer FAQ is available <a
    href="https://www.clawinfo.org/volunteer-faq">here</a>
  </li>
</ul>
<hr>
HTML;

  $content = [
    'battery-full' => ['Volunteer','$1<br><a href="'.$link3.'" role="button" class="btn btn-danger">Volunteer for 3 Shifts</a>'],
    'battery-half' => ['Volunteer','$99<br><a href="'.$link2.'" role="button" class="btn btn-danger">Volunteer for 2 Shifts</a>'],
    'user-tag' => ['Recruited Volunteer','If you are an approved Recruited Volunteer, please enter your coupon code above and click Start Registration or contact <a href="/planning/guest-services?category_id=19">guest services</a> to obtain your registration coupon.'],
  ];

  $tags = [
    ['<h4 class="fw-bold mb-0">','</h4>'],
    ['<p>','</p>']
  ];

  $result .= Bootstrap::writeGrid($content, $tags, true);

  return $result;
}

function vipHtml(): string {
  $vipEventId = clawEvents::getEventId(strtolower(Aliases::defaultPrefix.'-vip'));

  $content = [
    'ticket-alt' => ['Attendee Package','Includes over 150 events and exhibitors'],
    'utensils' => ['Reserved Seating at Meals','Optional reserved seating at all meals (purchase of separate meal tickets required)'],
    'kaaba' => ['Hospitality Suite Access',''],
    'concierge-bell' => ['Skip the Line','Personal delivery of badge and registration materials'],
    'glass-whiskey' => ['Drink Ticket Basket','Welcome basket at check-in, including $100 in beverage tickets'],
    'envelope-open-text' => ['President\'s Reception','You and your guest are invited'],
  ];

  $tags = [
    ['<h4 class="fw-bold mb-0">','</h4>'],
    ['<p><i>','</i></p>']
  ];

  $result = <<< HTML
<h2 class="pb-2">VIP Package</h2><hr>
<p>The VIP Package ($750) includes the following:</p>
HTML;

  $result .= Bootstrap::writeGrid($content, $tags, true);

  if ( Aliases::onsiteActive ) {
    $result .= '<div class="d-grid">
    <a role="button" href="javascript:;" class="btn btn-danger btn-lg">Come to Onsite Guest Services for Priority Registration</a>
    </div>';
    
  } else {
    $result .= <<< HTML
    <div class="d-grid gap-2">
      <p class="text-center">Click 'Express' for quickest checkout. For more options (meals, equipment rental, and speed dating) use 'Add Ons' button.</p>
      <a role="button" href="/index.php?option=com_eventbooking&view=register&event_id=$vipEventId" class="btn btn-danger btn-lg">Express VIP Checkout (no meals, $750)</a>
      <a role="button" href="/l23-reg-vip2" class="btn btn-danger btn-lg">Express VIP Checkout (with all 7 meals, $1,250)</a>
      <a role="button" href="/l23-reg-vip" class="btn btn-danger btn-lg">VIP Package with Manual Add Ons Selection ($750+)</a>
    </div>
HTML;
  }

  return $result;
}

function otherHtml(): string {
  return <<< HTML
  <p><b>If you are an Educator or VendorMart Crew,</b> please enter your coupon
  code above and click Start Registration. If you do not have your coupon, please
  contact your department coordinator or submit a Guest Services ticket <a href="/help?category_id=7">here.</a></p>

  <p>Day, Night, and VendorMart passes will be available for registration starting Wed., Nov 22, 2023.</p>
HTML;
}

function dayPassesHtml(): string {
  $e = new clawEvents(Aliases::current);
  $events = $e->getEventsByCategoryId(clawEvents::getCategoryIds(['day-passes']), $e->getClawEventInfo(),'event_date');

  date_default_timezone_set(Aliases::timezone);
  $now = date('Y-m-d H:i:s');
  $buttons = '';

  foreach( $events AS $e ) {
    if ( $now > $e->event_end_date ) continue;
    $eventAlias = $e->alias;
    $price = '$'. number_format($e->individual_price);
    $title = $e->title. '('.$price.')';
  
    $buttons .= <<< HTML
    <a role="button" href="/$eventAlias" class="btn btn-danger btn-lg">$title</a>
HTML;
  }

return <<<HTML
  <div class="container">
    <div class="row border border-3 border-info">
      <div class="col">
        <h1>Day Passes provide full access to events. Valid 9AM to 4AM (event day). Registrant must wear badge for event access.</h1>
      </div>
    </div>
  </div>

  <div class="mt-2 d-grid col-6 mx-auto gap-2">
    $buttons
  </div>
HTML;
}

function nightPassesHtml(): string {
  $e = new clawEvents(Aliases::current);
  $events = $e->getEventsByCategoryId(clawEvents::getCategoryIds(['PASSES']), $e->getClawEventInfo(),'event_date');

  date_default_timezone_set(Aliases::timezone);
  $now = date('Y-m-d H:i:s');
  $buttons = '';

  foreach( $events AS $e ) {
    if ( $now > $e->event_end_date ) continue;
    $eventId = $e->id;
    $price = '$'. number_format($e->individual_price);
    $title = $e->title. '('.$price.')';

    $color = 'btn-success';
    if ( strpos($title, 'Night') !== false ) $color = 'btn-info';
    if ( strpos($title, 'Weekend Night') !== false ) $color = 'btn-warning';
  
    $buttons .= <<< HTML
    <a role="button" href="/index.php?option=com_eventbooking&view=register&event_id=$eventId" class="btn $color btn-lg">$title</a>
HTML;
  }

return <<<HTML
  <div class="container">
    <div class="row border border-3 border-info">
      <div class="col">
        <h1>Night passes valid after 7PM. Registrant must wear Night Pass wristband for event access. Night passes do not include any BDSM Parties.</h1>
      </div>
    </div>
  </div>
  <div class="mt-2 d-grid col-6 mx-auto gap-2">
    $buttons
  </div>
HTML;
}