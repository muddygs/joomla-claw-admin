<?php

use ClawCorpLib\Enums\EbPublishedState;
use ClawCorpLib\Enums\PackageInfoTypes;
use ClawCorpLib\Helpers\EventBooking;

date_default_timezone_set($this->eventConfig->eventInfo->timezone);
$now = date('Y-m-d H:i:s');

?>

<div class="row border border-3 border-info">
  <div class="col">
    <h1 class="text-center">Night passes valid after 7PM. Registrant must wear Night Pass wristband for event access. Night passes do not include any BDSM Parties.</h1>
  </div>
</div>

<div class="mt-2 d-grid col-6 mx-auto gap-2 mb-2">

  <?php
  /** @var \ClawCorpLib\Lib\PackageInfo */
  foreach ($this->eventConfig->packageInfos as $packageInfo) {
    if (
      $now > $packageInfo->end
      || $packageInfo->packageInfoType != PackageInfoTypes::passes
      || $packageInfo->published != EbPublishedState::published
    ) continue;

    $linkFull = EventBooking::buildIndividualLink($packageInfo);

    $price = '$' . number_format($packageInfo->fee);
    $title = $packageInfo->title . ' (' . $price . ')';

  ?>
    <a role="button" href="<?= $linkFull ?>" class="btn btn-success btn-lg"><?= $title ?></a>
  <?php
  }
  ?>
</div>
