<?php
defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;
use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Helpers\Locations;
use Joomla\CMS\HTML\HTMLHelper;

$tabInfo = $this->list->tabs->{$this->tabId};

if (!count($tabInfo['ids'])) {
  echo '<p>No classes this period.</p>';
  return;
}

$prevTimeSlot = '';

foreach ($tabInfo['ids'] as $classId) {
  $class = $this->list->items[$classId];

  if (array_key_exists($class->time_slot, $this->time_slots)) {
    $timeSlot = $this->time_slots[$class->time_slot];
  } else {
    continue;
  }

  $room = Locations::GetLocationById($class->location)->value;

  // Merge presenters
  $presenter_urls = [];
  $owner = true;

  foreach ($this->list->items[$classId]->presenter_info as $presenter) {
    $link = HTMLHelper::link(
      Route::_('index.php?option=com_claw&view=skillspresenter&id=' . $presenter['uid']) . '&tab=' . $this->tabId,
      $presenter['name'],
      $owner ? ['class' => 'fs-5'] : ['class' => 'fw-light']
    );

    $presenter_urls[] = $link;
    $owner = false;
  }

  $presenter_links = implode('<br/>', $presenter_urls);

  // Class title to detail link
  $title = HTMLHelper::link(
    Route::_('index.php?option=com_claw&view=skillsclass&id=' . $class->id) . '&tab=' . $this->tabId,
    $class->track ?
      '<span class="badge rounded-pill text-bg-success">' . strtoupper($class->track) . '</span>&nbsp;' . $class->title :
      $class->title,
    ['class' => 'fs-5']
  );

  if ($prevTimeSlot != $timeSlot) {
    if ($prevTimeSlot != '') echo "</div><hr>";

    $prevTimeSlot = $timeSlot;

?>
    <h2 class="text-center"><?= $timeSlot ?></h2>
    <div class="container skills">
      <div class="row row-striped">
        <div class="col-8 col-lg-5 pt-0 pb-0 pt-lg-2 pb-lg-2 mt-2 mt-lg-1 mb-2 mb-lg-1 font-weight-bold tight">Title</div>
        <div class="col-4 col-lg-3 pt-0 pb-0 pt-lg-2 pb-lg-2 mt-2 mt-lg-1 mb-2 mb-lg-1 font-weight-bold tight">Room</div>
        <div class="col-8 col-lg-3 pt-0 pb-0 pt-lg-2 pb-lg-2 mt-2 mt-lg-1 mb-2 mb-lg-1 font-weight-bold tight">Presenter(s)</div>
        <div class="col-4 col-lg-1 pt-0 pb-0 pt-lg-2 pb-lg-2 mt-2 mt-lg-1 mb-2 mb-lg-1 font-weight-bold tight">Survey</div>
      </div>
    <?php
  }

  $survey = '<i class="fa fa-comments fa-2x text-dark" data-bs-toggle="tooltip" data-bs-placement="top" title="Surveys are not open"></i>';

  if ($this->list->survey != '' && $this->enable_surveys ) {
    $link = $this->list->survey . '&form[classTitleParam]=' . $class->id;
    $survey = '<a href="' . $link . '" style="color:#ffae00"><i class="fa fa-comments fa-2x"></i></a>';
  }

    ?>
    <div class="row row-striped">
      <div class="col-8 col-lg-5 pt-1 pb-1 mt-2 mt-lg-1 mb-2 mb-lg-1"><?= $title ?>&nbsp;<i class="fa fa-chevron-right"></i></div>
      <div class="col-4 col-lg-3 pt-1 pb-1 mt-2 mt-lg-1 mb-2 mb-lg-1"><?php if ($this->include_room) echo $room; ?></div>
      <div class="col-8 col-lg-3 pt-1 pb-1 mt-2 mt-lg-1 mb-2 mb-lg-1"><?= $presenter_links ?></div>
      <div class="col-4 col-lg-1 pt-1 pb-1 mt-2 mt-lg-1 mb-2 mb-lg-1"><?= $survey ?></div>
    </div>
  <?php
}
  ?>
    </div>