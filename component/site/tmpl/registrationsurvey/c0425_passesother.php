<?php

use ClawCorpLib\Enums\EbPublishedState;
use ClawCorpLib\Enums\PackageInfoTypes;
use ClawCorpLib\Helpers\EventBooking;

date_default_timezone_set($this->eventConfig->eventInfo->timezone);
$now = date('Y-m-d H:i:s');

?>

<div class="row border border-3 border-info mb-2">
  <p class="text-center mt-4 display-6"><strong>Access Passes</strong></p>
</div>

<div class="mt-2 row row-cols-1 row-cols-md-2 row-cols-lg-4">

  <?php
  /** @var \ClawCorpLib\Lib\PackageInfo */
  foreach ($this->eventConfig->packageInfos as $packageInfo) {
    if (
      $now > $packageInfo->end
      || $packageInfo->packageInfoType != PackageInfoTypes::passes_other
      || $packageInfo->published != EbPublishedState::published
      || $packageInfo->eventId == 0
    ) continue;

    $linkFull = EventBooking::buildIndividualLink($packageInfo);

    $price = '$' . number_format($packageInfo->fee);

  ?>
    <div class="col card border-warning p-2 mb-2" style="background-color: transparent;">
      <div class="card-header">
        <strong><?= $packageInfo->title ?></strong>
      </div>
      <div class="card-body">
        <p class="card-text"><?= $packageInfo->description ?></p>
      </div>
      <div class="card-footer">
        <a role="button" href="<?= $linkFull ?>" class="btn btn-danger btn-lg w-100">
          <?= $price ?>
        </a>
      </div>
    </div>
  <?php
  }
  ?>
</div>
