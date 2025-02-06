<?php

\defined('_JEXEC') or die;

?>
<p>Type in the first few letters of the LAST NAME or Badge # (C21-01234 or 01234)</p>

<form method="post" name="claw-process-badge-checkin" id="claw-badge-checkin" class="form-horizontal">
  <fieldset class="form-group">
    <div class="row">
      <legend class="col-form-label col-3">Search by Name or Badge #:</legend>
      <div class="col-3">
        <input name="search" id="search" value="" placeholder="" maxlength="15" size="15" type="text"
          hx-post="/index.php?option=com_claw&task=checkin.search&format=raw"
          hx-trigger="input changed delay:500ms, keyup[key=='Enter'], load"
          hx-target="#searchresults" />
      </div>
      <div class="col-6">
        <button class="btn btn-info" name="clear" id="clear" onClick="">
          Clear
        </button>
      </div>
    </div>
  </fieldset>

  <fieldset class="form-group">
    <div class="row">
      <legend class="col-form-label col-3">Search Results:</legend>
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

  <input type="hidden" id="registration_code" value="" />

  <h4 id="errorMsg"></h4>
  <h4 id="infoMsg"></h4>

  <div class="form-group" id="form-print-buttons">
    <div class="row">
      <div class="col">
        <input name="submit" id="submit" type="button" value="Confirm and Issue Badge" class="btn btn-danger mb-2" style="display:none;" onclick="doCheckin()" />
      </div>
    </div>
  </div>

  <input type="hidden" name="token" id="token" value="<?= $this->token ?>" />
  <input type="hidden" name="page" id="page" value="<?= $this->page ?>" />
</form>
