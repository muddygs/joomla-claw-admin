<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2025 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

// name="info" used to clear in typescript

/** @var \ClawCorpLib\Checkin\Record */
$record = $this->record;
?>

<div class="container my-3" id="record" name="record">
  <h4 id="errorMsg" name="info">
    <?php if (!empty($this->error)): ?>
      <pre><?= $this->error ?></pre>
    <?php endif; ?>
  </h4>

  <h4 id="infoMsg">
  </h4>

  <?php if ($this->isValid ?? false): ?>
    <div class="form-group" id="form-print-buttons">
      <div class="row">
        <div class="col">
          <input name="issue" id="issue" type="button" value="Confirm and Issue Badge" class="btn btn-lg btn-success w-100 mb-2"
            hx-target="this"
            hx-on::before-request='clearDisplay();'
            hx-swap="outerHTML"
            hx-post="/index.php?option=com_claw&task=checkin.issue&format=raw" />
        </div>
      </div>
    </div>
  <?php endif; ?>

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
        <?php if ($record->printed): ?>
          <div class="col-4 fw-bold text-center text-bg-success" id="printed" name="info">
            Printed
          </div>
        <?php else: ?>
          <div class="col-4 fw-bold text-center text-bg-danger" id="printed" name="info">
            Need to Print
          </div>
        <?php endif; ?>
        <?php if ($record->issued): ?>
          <div class="col-3 fw-bold text-center text-bg-success" id="issued" name="info">
            Issued
          </div>
        <?php else: ?>
          <div class="col-3 fw-bold text-center text-bg-danger" id="issued" name="info">
            Not Issued
          </div>
        <?php endif; ?>
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
          <?= $record->clawPackage ?>
        </div>
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

  <input type="hidden" id="registration_code" value="<?= $record->registration_code ?>" />

</div>
