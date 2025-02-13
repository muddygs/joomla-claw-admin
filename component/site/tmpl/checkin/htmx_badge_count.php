<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2025 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

if ($this->placeholder ?? false) {
  $this->attendeecount = $this->volunteercount = $this->othercount = $this->totalcount = 'Loading...';
}
?>

<div id="badgeCounts" hx-post="index.php?option=com_claw&task=checkin.count&format=raw" hx-trigger="load delay:60s" hx-swap="outerHTML">
  <p>Total badges to print:
    <b>
      <?= $this->totalcount ?>
    </b>
  </p>
  <div class="row">
    <div class="col-4">
      <h2>Attendee</h2>
      <p>To print: <b><?= $this->attendeecount ?></b></p>
    </div>
    <div class="col-4">
      <h2>Volunteer</h2>
      <p>To print: <b><?= $this->volunteercount ?></b></p>
    </div>
    <div class="col-4">
      <h2>Other (full color)</h2>
      <p>To print: <b><?= $this->othercount ?></b></p>
    </div>
  </div>
</div>
