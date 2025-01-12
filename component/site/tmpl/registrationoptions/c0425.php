<?php
defined('_JEXEC') or die;

use ClawCorpLib\Helpers\Bootstrap;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;

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
    <b>You are already registered. To view all your registrations, click <a href="/planning/my-reg">here</a></b> to view My Registrations.
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

$this->categories = $this->mealCategoryIds;
if (count($this->categories)) {
  $headings[] = 'Meals';
  $content[] = $this->loadTemplate('categories');
}

$this->categories = $this->eventConfig->eventInfo->eb_cat_speeddating;
if (count($this->categories)) {
  $headings[] = 'Speed Dating';
  $content[] = $this->loadTemplate('categories');
}

if (!$this->eventConfig->eventInfo->onsiteActive) {
  $this->categories = $this->eventConfig->eventInfo->eb_cat_equipment;

  if (count($this->categories)) {
    $headings[] = 'Rentals';
    $content[] = '<p>Equipment rentals available onsite during onsite registration.</p>';
  }
}

$headings[] = 'Community';
$content[] = $this->loadTemplate('heart');

Bootstrap::writePillTabs($headings, $content, $this->tab);

echo $this->loadTemplate('footer');
