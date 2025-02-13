<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2025 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;
\defined('_JEXEC') or die;

?>
<p>Type in the first few letters of the LAST NAME or Badge # (C21-01234 or 01234)</p>

<form method="post" name="claw-process-badge-checkin" id="claw-badge-checkin" class="form-horizontal">
  <fieldset class="form-group">
    <div class="row mb-1">
      <legend class="col-form-label col-3">Search by Last Name or Badge #:</legend>
      <div class="col-3">
        <input name="search" id="search" value="" placeholder="" maxlength="15" size="15" type="text" autocomplete="off"
          hx-post="/index.php?option=com_claw&task=checkin.search&format=raw"
          hx-trigger="input changed delay:500ms, keyup[key=='Enter'], load"
          hx-target="#searchresults" hx-swap="outerHTML" />
      </div>
      <div class="col-6">
        <button type=button" class="btn btn-lg btn-info" name="clear" id="clear" onClick="clearDisplay(); return false;">
          Clear
        </button>
      </div>
    </div>
  </fieldset>

  <fieldset class="form-group">
    <div class="row">
      <legend class="col-form-label col-3">Search Results (ordered by first name):</legend>
      <div class="col-9">
        <select name="searchresults" id="searchresults" size="10" style="height:auto">
        </select>
      </div>
    </div>
  </fieldset>


  <?php
  // Display for consistency in interface
  $this->setLayout('htmx_search_results');
  echo $this->loadTemplate();
  ?>


  <input type="hidden" name="token" id="token" value="<?= $this->token ?>" />
  <input type="hidden" name="page" id="page" value="<?= $this->page ?>" />
</form>
