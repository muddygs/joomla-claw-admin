<?php

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use ClawCorpLib\Lib\Jwtwrapper;

Jwtwrapper::redirectOnInvalidToken('badge-checkin', $this->token);

/** @var Joomla\CMS\Application\SiteApplication */
$app = Factory::getApplication();
/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $app->getDocument()->getWebAssetManager();
$wa->useScript('com_claw.jwtmon');
$wa->useScript('com_claw.checkin');

?>
<div class="mb-2 p-1 bg-info text-white" id="jwtstatus"></div>

<h1>Badge Checkin Station</h1>
<p>Type in the first few letters of the LAST NAME or Badge # (C21-01234 or 01234)</p>

<form method="post" name="claw-process-badge-checkin" id="claw-badge-checkin" class="form-horizontal">
  <fieldset class="form-group">
    <div class="row">
      <legend class="col-form-label col-3">Search by Name or Badge #:</legend>
      <div class="col-3">
        <input name="search" id="search" value="" placeholder="" maxlength="15" size="15" class="" type="text" />
      </div>
      <div class="col-6"></div>
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

  <div class="container my-3">
    <div class="row">
      <div class="col-6 border border-danger py-2">
        <div class="row">
          <div class="col-4">Badge #</div>
          <div class="col-8 fw-bold" id="badgeId" name="info" style="color:#ffae00"></div>
        </div>
      </div>
      <div class="col-6 border border-danger py-2">
        <div class="row">
          <div class="col-4">Status</div>
          <div class="col-4 fw-bold" id="printed" name="info" style="color:#ffae00"></div>
          <div class="col-3 fw-bold text-center" id="issued" name="info" style="color:green; background-color:#fff"></div>
          <div class="col-1"></div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-6 border border-danger py-2">
        <div class="row">
          <div class="col-4">Legal Name</div>
          <div class="col-8 fw-bold" id="legalName" name="info" style="color:#ffae00"></div>
        </div>
      </div>
      <div class="col-6 border border-danger py-2">
        <div class="row">
          <div class="col-4">Package Type</div>
          <div class="col-8 fw-bold" id="clawPackage" name="info" style="color:#ffae00"></div>
        </div>
      </div>
    </div>

    <div class="row mb-1">
      <div class="col-6 border border-danger py-2">
        <div class="row">
          <div class="col-4">City</div>
          <div class="col-8 fw-bold" id="city" name="info" style="color:#ffae00"></div>
        </div>
      </div>
      <div class="col-6 border border-danger py-2">
        <div class="row">
          <div class="col-4">Shirt Size</div>
          <div class="col-8 fw-bold" id="shirtSize" name="info" style="color:#ffae00"></div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-4 border border-danger py-2">
        <div class="row">
          <div class="col-4">Dinner</div>
          <div class="col-8 fw-bold" id="dinner" name="info" style="color:#ffae00"></div>
        </div>
      </div>
      <div class="col-4 border border-danger py-2">
        <div class="row">
          <div class="col-4">Brunch</div>
          <div class="col-8 fw-bold" id="brunch" name="info" style="color:#ffae00"></div>
        </div>
      </div>
      <div class="col-4 border border-danger py-2">
        <div class="row">
          <div class="col-4">Buffets</div>
          <div class="col-8 fw-bold" id="buffets" name="info" style="color:#ffae00" colspan="3"></div>
        </div>
      </div>
    </div>

    <div class="row border border-danger py-2 mt-1">
      <div class="col-2">Volunteer Shifts</div>
      <div class="col-10" id="shifts" name="info" style="color:#ffae00" colspan="3"></div>
    </div>
  </div>

  <input type="hidden" id="registration_code" value="" />

  <h4 id="errorMsg"></h4>
  <h4 id="infoMsg"></h4>

  <div class="form-group" id="form-print-buttons">
    <div class="row">
      <div class="col">
        <input name="submit" id="submit" type="button" value="Confirm and Issue Badge" class="btn btn-danger mb-2 d-none" />
      </div>
    </div>
  </div>

  <input type="hidden" name="token" id="token" value="<?= $this->token ?>" />
</form>

<div id="status"></div>