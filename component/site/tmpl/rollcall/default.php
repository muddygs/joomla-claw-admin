<?php

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use ClawCorpLib\Lib\Jwtwrapper;
use Joomla\CMS\Layout\LayoutHelper;

// NOTE: this is the JWT token, not the session token
Jwtwrapper::redirectOnInvalidToken('volunteer-roll-call', $this->token);

/** @var Joomla\CMS\Application\SiteApplication */
$app = Factory::getApplication();
/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $app->getDocument()->getWebAssetManager();
$wa->useScript('com_claw.jwtmon');
$wa->useScript('com_claw.rollcall');
$wa->useStyle('com_claw.admin');

$displayData['items'] = $this->items;
$displayData['htmx'] = true;
$displayData['title'] = 'Select Shift to Add Below';

?>
<div class="mb-2 p-1 bg-info text-white" id="jwtstatus"></div>

<h1>Volunteer Checkin</h1>
<p>Type in the Badge # (C25-01234)</p>

<form
  action="#"
  method="post"
  name="claw-rollcall"
  id="claw-rollcall"
  class="form-horizontal row g-3">
  <fieldset class="form-group">
    <div class="row">
      <legend class="col-form-label col-3">Badge #:</legend>
      <div class="col-3">
        <input name="regid" id="regid" value="" placeholder="" maxlength="15" size="15" class="" type="text" />
      </div>
      <div class="col-3">
        <input name="rollcallSearch" id="rollcallSearch" type="button" value="Search" class="btn btn-danger"
          hx-post="/index.php?option=com_claw&task=rollcall.rollcallSearch&format=raw"
          hx-target="#shifts"
          onClick="document.getElementById('regid').readOnly=true" />
        <button class="btn btn-success" type="button" onclick="clearVolunteerData();">Clear</button>
      </div>
      <div class="col-3">
        <h1>
          <div class="col-6" id="name"></div>
        </h1>
      </div>
    </div>
  </fieldset>

  <div id="shifts"
    hx-post="/index.php?option=com_claw&task=rollcall.rollcallOverview&format=raw"
    hx-trigger="updateOverview from:body"
    hx-target="#overview">
  </div>

  <div id="status"></div>

  <hr />

  <div id="overview">
    <?= LayoutHelper::render('claw.volunteer_overview', $displayData) ?>
  </div>

  <input type="hidden" name="uid" id="uid" value="0" />
  <input type="hidden" name="token" id="token" value="<?= $this->token ?>" />
</form>
<?php
