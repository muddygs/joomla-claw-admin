<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2025 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */


foreach ($this->records as $record) {
  $inClass = 'btn-danger';
  $inConfirm = '';
  $inAction = 1; // For SET
  $outClass = 'btn-danger';
  $outConfirm = '';
  $outAction = 1;

  if ($record['checkin']) {
    $inClass = 'btn-success';
    $inConfirm = 'hx-confirm="Clear the Check In and Out?"';
    $inAction = 0; // For CLEAR
  }

  if ($record['checkout']) {
    $outClass = 'btn-success';
    $outConfirm = 'hx-confirm="Clear the Check Out?"';
    $outAction = 0;
  }

?> <div class="row row-striped align-items-center">
    <div class="col-4 col-lg-6">
      <?= $record['title'] ?>
    </div>
    <div class="col-4 col-lg-3">
      <button type="button"
        id="in-<?= $record['regid'] ?>"
        name="in-<?= $record['regid'] ?>"
        class="btn btn-lg mt-2 mb-2 <?= $inClass ?>"
        <?= $inConfirm ?>
        hx-vals='{"action":"<?= $inAction ?>"}'
        hx-post="/index.php?option=com_claw&task=rollcall.rollcallToggle&format=raw"
        hx-target="#shifts">
        IN
      </button>
    </div>
    <div class="col-4 col-lg-3">
      <button type="button"
        id="out-<?= $record['regid'] ?>"
        name="out-<?= $record['regid'] ?>"
        class="btn btn-lg mt-2 mb-2 <?= $outClass ?>"
        <?= $outConfirm ?>
        hx-vals='{"action":"<?= $outAction ?>"}'
        hx-post="/index.php?option=com_claw&task=rollcall.rollcallToggle&format=raw"
        hx-target="#shifts">
        OUT
      </button>
    </div>
  </div>
<?php
}
