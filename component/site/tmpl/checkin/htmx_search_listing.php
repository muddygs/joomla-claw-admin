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

?>
<select id="searchresults" name="searchresults" size="10" style="height:auto"
  hx-post="/index.php?option=com_claw&task=checkin.value&format=raw"
  hx-trigger="keyup"
  hx-target="#record"
  hx-swap="outerHTML">

  <?php foreach ($this->data as $data): ?>
    <option value="<?= $data['id'] ?>"
      hx-post="/index.php?option=com_claw&task=checkin.value&format=raw"
      hx-target="#record"
      hx-swap="outerHTML">
      <?= $data['name'] ?>
    </option>
  <?php endforeach; ?>

</select>
