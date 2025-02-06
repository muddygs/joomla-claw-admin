<?php

\defined('_JEXEC') or die;

use \ClawCorpLib\Checkin\Record;

$record = new Record();

if (property_exists($this, 'record')) {
  /** @var \ClawCorpLib\Checkin\Record */
  $record = $this->record;
}

?>

<div class="container my-3">
  <div class="row">
    <div class="col-6 border border-danger py-2">
      <div class="row">
        <div class="col-4">Badge #</div>
        <div class="col-8 fw-bold" id="badgeId" name="info" style="color:#ffae00"><?= $record->badgeId ?></div>
      </div>
    </div>
    <div class="col-6 border border-danger py-2">
      <div class="row">
        <div class="col-4">Status</div>
        <div class="col-4 fw-bold" id="printed" name="info" style="color:#ffae00"></div>
        <div class="col-3 fw-bold text-center" id="issued" name="info" style="color:green; background-color:#fff"><?= $record->issued ?></div>
        <div class="col-1"></div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-6 border border-danger py-2">
      <div class="row">
        <div class="col-4">Legal Name</div>
        <div class="col-8 fw-bold" id="legalName" name="info" style="color:#ffae00"><?= $record->legalName ?></div>
      </div>
    </div>
    <div class="col-6 border border-danger py-2">
      <div class="row">
        <div class="col-4">Package Type</div>
        <div class="col-8 fw-bold" id="clawPackage" name="info" style="color:#ffae00">
          <<?= $record->clawPackage ?> /div>
        </div>
      </div>
    </div>

    <div class="row mb-1">
      <div class="col-6 border border-danger py-2">
        <div class="row">
          <div class="col-4">City</div>
          <div class="col-8 fw-bold" id="city" name="info" style="color:#ffae00"><?= $record->city ?></div>
        </div>
      </div>
      <div class="col-6 border border-danger py-2">
        <div class="row">
          <div class="col-4">Shirt Size</div>
          <div class="col-8 fw-bold" id="shirtSize" name="info" style="color:#ffae00"><?= $record->shirtSize ?></div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-4 border border-danger py-2">
        <div class="row">
          <div class="col-4">Dinner</div>
          <div class="col-8 fw-bold" id="dinner" name="info" style="color:#ffae00"><?= $record->dinner ?></div>
        </div>
      </div>
      <div class="col-4 border border-danger py-2">
        <div class="row">
          <div class="col-4">Brunch</div>
          <div class="col-8 fw-bold" id="brunch" name="info" style="color:#ffae00"><?= $record->brunch ?></div>
        </div>
      </div>
      <div class="col-4 border border-danger py-2">
        <div class="row">
          <div class="col-4">Buffets</div>
          <div class="col-8 fw-bold" id="buffets" name="info" style="color:#ffae00" colspan="3"><?= $record->buffets ?></div>
        </div>
      </div>
    </div>

    <div class="row border border-danger py-2 mt-1">
      <div class="col-2">Volunteer Shifts</div>
      <div class="col-10" id="shifts" name="info" style="color:#ffae00" colspan="3"><?= $record->shifts ?></div>
    </div>
  </div>
