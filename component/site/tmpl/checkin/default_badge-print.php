<?php

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use ClawCorpLib\Lib\Jwtwrapper;
use ClawCorpLib\Lib\Checkin;

$decoded = Jwtwrapper::redirectOnInvalidToken('badge-print');

// TO DO LIST
// Probably will want to remove form action from badgeStationCheckin.php

// Determine count of badges to print for batch printing
$toPrint = Checkin::getUnprintedBadgeCount();

/** @var Joomla\CMS\Application\SiteApplication */
$app = Factory::getApplication();
/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $app->getDocument()->getWebAssetManager();
$wa->useScript('com_claw.jwtmon');
$wa->useScript('com_claw.checkin');

?>
<div class="mb-2 p-1 bg-info text-white" id="jwtstatus"></div>
<h1>Batch Printing</h1>
<p>Badges to print: <b id="badgeCount"><?php echo $toPrint ?></b></p>
<form method="post" name="claw-process-badge-batch" id="claw-process-badge-batch" class="row g-3">
  <div class="col-3">
    <label for="batchcount" class="form-label">Enter # of Badges to Print (1-50)</label>
    <input type="number" name="batchcount" id="batchcount" min="1" max="50" step="1" value="10" />
  </div>
  <div class="col-12">
    <button type="button" class="btn btn-primary" id="submitBatch">Generate Badges</button>
  </div>
</form>

<hr />

<h1>Individual Badge Printing</h1>
<p>Type in the first few letters of the LAST NAME or Badge # (C21-1234 or 1234)</p>

<form method="post" name="claw-process-badge-print" id="claw-process-badge-print" class="form-horizontal">
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

  <h2 id='errorMsg'></h2>

  <div class="form-group" id="form-print-buttons">
    <div class="row">
      <div class="col">
        <input name="submitPrint" id="submitPrint" type="button" value="Print Badge" class="btn btn-danger mb-2 w-100" style="display:none;" />
      </div>
      <div class="col">
        <input name="submitPrintIssue" id="submitPrintIssue" type="button" value="Issue + Print Badge" class="btn btn-info mb-2 w-100" style="display:none;" />
      </div>
    </div>
  </div>

  <input type="hidden" name="token" id="token" value="<?= $decoded->token ?>" />
</form>

<div id="status"></div>