<?php
defined('_JEXEC') or die;

use ClawCorpLib\Helpers\Bootstrap;

$eventLayout = $this->getLayout();
$this->setLayout('common');
echo $this->loadTemplate('toast');
echo $this->loadTemplate('header');

?>
<h2 class="rstpl-title-left text-white">Add On Packages</h2>
<div class="border border-2 border-info rounded mb-5">
  <h3 class="m-2 text-center">One Registration Per Person. Any addons must be purchased <u>per registration</u>.</h3>
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
    $content[] = $this->loadTemplate('categories');
  }
}

$headings[] = 'Community';
$content[] = $this->loadTemplate('heart');

Bootstrap::writePillTabs($headings, $content, $this->tab);

echo $this->loadTemplate('footer');
