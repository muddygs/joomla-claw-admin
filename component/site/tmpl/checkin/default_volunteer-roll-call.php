<?php

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use ClawCorpLib\Lib\Jwtwrapper;

Jwtwrapper::redirectOnInvalidToken('volunteer-roll-call', $this->token);

/** @var Joomla\CMS\Application\SiteApplication */
$app = Factory::getApplication();
/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $app->getDocument()->getWebAssetManager();
$wa->useScript('com_claw.jwtmon');
$wa->useScript('com_claw.rollcall');
$wa->useStyle('com_claw.admin');
//$wa->useScript('choicesjs');
//$wa->useStyle('choicesjs');


?>
<div class="mb-2 p-1 bg-info text-white" id="jwtstatus"></div>

<h1>Volunteer Checkin</h1>
<p>Type in the first few letters of the LAST NAME or Badge # (C21-01234 or 01234)</p>

<form method="post" name="claw-rollcall" id="claw-rollcall" class="form-horizontal">
  <fieldset class="form-group">
    <div class="row">
      <legend class="col-form-label col-3">Badge #:</legend>
      <div class="col-3">
        <input name="regid" id="regid" value="" placeholder="" maxlength="15" size="15" class="" type="text"/>
      </div>
      <div class="col-3">
        <button class="btn btn-danger" type="button" onclick="fetchVolunteerData();">Search</button>
        <button class="btn btn-success" type="button" onclick="clearVolunteerData();">Clear</button>
      </div>
      <div class="col-3"><h1><div class="col-6" id="name"></div></h1></div>
    </div>
  </fieldset>

  <div id="shifts"></div>

  <hr/>

  <fieldset class="form-group">
    <p class="text-danger">Warning: no checks in place to prevent duplicate shifts.</p>
    <div class="row">
      <div class="col-9">
        <select name="shift-items" id="shift-items" class="form-control col-9">
          <option value="0">Select a shift</option>
          <?php
          foreach ( $this->shifts as $shift ) {
            echo '<option value="' . $shift['id'] . '">' . $shift['title'] . '</option>';
          }
          ?>
        </select>
      </div>
      <div class="col-3">
        <button class="btn btn-info" type="button" onclick="addShift()">Add Shift</button>
      </div>
    </div>
  </fieldset>

  <input type="hidden" name="uid" id="uid" value="0" />
  <input type="hidden" name="token" id="token" value="<?= $this->token ?>" />
</form>

<div id="status"></div>