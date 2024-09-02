<?php
defined('_JEXEC') or die;

use ClawCorpLib\Enums\ConfigFieldNames;
use ClawCorpLib\Enums\EventPackageTypes;

// *sigh* not namespaced
require_once(JPATH_ROOT . '/components/com_eventbooking/helper/cart.php');
require_once(JPATH_ROOT . '/components/com_eventbooking/helper/database.php');
require_once(JPATH_ROOT . '/components/com_eventbooking/helper/helper.php');

use ClawCorpLib\Helpers\Bootstrap;
use ClawCorpLib\Helpers\Config;
use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Lib\ClawEvents;
use ClawCorpLib\Lib\EventInfo;
use Joomla\CMS\HTML\HTMLHelper;

$vipRedirect = $this->vipRedirect;

if (EventPackageTypes::vip2 == $this->eventPackageType) {
  $this->eventPackageType == EventPackageTypes::vip;
  $vipRedirect = true;
}

?>
<h1 class="text-center">Registration Options for <?= $this->eventConfig->eventInfo->description ?></h1>
<?php


$eventLayout = $this->getLayout();
$this->setLayout('common');
echo $this->loadTemplate('toast');
#$this->setLayout($eventLayout);

$eventInfo = $this->eventConfig->eventInfo;

?>
<div class="container">
  <div class="row">
    <div class="col-6 col-lg-2">
      <div class="d-grid">
        <a href="<?= $this->registrationSurveyLink ?>" class="btn btn-danger" role="button"><i class="fa fa-chevron-left"></i> Back</a>
      </div>
    </div>
    <div class="col-6 col-lg-6">
      <h1><?= $this->eventDescription ?></h1>
      <?php
      if ($this->coupon != ''):
      ?>
        <p style="margin-bottom:0px !important;">Your Coupon Code: <strong><?= $this->coupon ?></strong></p>
      <?php
      endif;
      ?>


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

if ($this->mainEvent != null) :
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
$content[] = $this->loadTemplate('shifts');
$headings[] = 'Meals';
$content[] = contentMeals($this->eventConfig->eventInfo);

$headings[] = 'Speed Dating';
$content[] = contentSpeedDating($this->eventConfig->eventInfo);

if (!$this->eventConfig->eventInfo->onsiteActive) {
  $headings[] = 'Rentals';
  $content[] = contentRentals($this->eventConfig->eventInfo);
}

$headings[] = 'Community';
$content[] = contentLeatherHeart($this->eventConfig->eventInfo);

Bootstrap::writePillTabs($headings, $content, $this->tab);

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

function contentShifts(EventInfo $eventInfo, EventPackageTypes $EventPackageType): string
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
  if ($EventPackageType == EventPackageTypes::volunteersuper) {
    $categoryIds = array_merge($categoryIds, $eventInfo->eb_cat_supershifts);
  }

  $config = new Config($eventInfo->alias);
  $baseURL = $config->getConfigText(ConfigFieldNames::CONFIG_URLPREFIX, 'shifts');

  if (null == $baseURL) {
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

  if (! $eventInfo->onsiteActive) {
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
