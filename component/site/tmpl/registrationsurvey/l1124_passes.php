<?php

use ClawCorpLib\Enums\PackageInfoTypes;
use ClawCorpLib\Helpers\EventBooking;

date_default_timezone_set($this->eventConfig->eventInfo->timezone);
$now = date('Y-m-d H:i:s');

?>

<div class="container">
  <div class="row border border-3 border-info">
    <div class="col">
      <h1>Night passes valid after 7PM. Registrant must wear Night Pass wristband for event access. Night passes do not include any BDSM Parties.</h1>
    </div>
  </div>
</div>

<div class="mt-2 d-grid col-6 mx-auto gap-2">

  <?php
  /** @var \ClawCorpLib\Lib\PackageInfo */
  foreach ($this->eventConfig->packageInfos as $packageInfo) {
    if ($now > $packageInfo->end) continue;
    if ($packageInfo->packageInfoType != PackageInfoTypes::passes) continue;

    $linkFull = EventBooking::buildRegistrationLink($this->eventConfig->alias, $packageInfo->eventPackageType);

    $price = '$' . number_format($packageInfo->fee);
    $title = $packageInfo->title . ' (' . $price . ')';

    $color = 'btn-success';
    if (strpos($title, 'Night') !== false) $color = 'btn-info';
    if (strpos($title, 'Weekend Night') !== false) $color = 'btn-warning';

  ?>
    <a role="button" href="<?= $linkFull ?>" class="btn <?= $color ?> btn-lg"><?= $title ?></a>
  <?php
  }
  ?>
</div>
