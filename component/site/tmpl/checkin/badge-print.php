<?php

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use ClawCorpLib\Lib\Jwtwrapper;

Jwtwrapper::redirectOnInvalidToken('badge-print', $this->token);

/** @var Joomla\CMS\Application\SiteApplication */
$app = Factory::getApplication();
/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $app->getDocument()->getWebAssetManager();
$wa->useScript('com_claw.jwtmon');
$wa->useScript('com_claw.checkin');
$wa->useStyle('com_claw.admin');

?>
<div class="mb-2 p-1 bg-info text-white" id="jwtstatus"></div>
<h1 class="text-danger">NEW Batch Printing</h1>
<p>Total badges to print: <b id="badgeCount"></b></p>

<form method="post" name="claw-process-badge-batch" id="claw-process-badge-batch">
  <div class="row">
    <div class="col-4">
      <h2>Attendee</h2>
      <p>To print: <b id="attendeeCount"></b></p>
      <label for="batchcount1" class="form-label">Enter # of Badges to Print (1-50)</label>
      <input type="number" name="batchcount1" id="batchcount1" min="1" max="50" step="1" value="10" />
      <button type="button" class="btn btn-primary" id="submitBatch" onclick="doBatchPrint(1);">Generate Badges</button>
    </div>
    <div class="col-4">
      <h2>Volunteer</h2>
      <p>To print: <b id="volunteerCount"></b></p>
      <label for="batchcount2" class="form-label">Enter # of Badges to Print (1-50)</label>
      <input type="number" name="batchcount2" id="batchcount2" min="1" max="50" step="1" value="10" />
      <button type="button" class="btn btn-primary" id="submitBatch" onclick="doBatchPrint(2);">Generate Badges</button>
    </div>
    <div class="col-4">
      <h2>Others</h2>
      <p>To print: <b id="remainderCount"></b></p>
      <label for="batchcount0" class="form-label">Enter # of Badges to Print (1-50)</label>
      <input type="number" name="batchcount0" id="batchcount0" min="1" max="50" step="1" value="10" />
      <button type="button" class="btn btn-primary" id="submitBatch" onclick="doBatchPrint(0);">Generate Badges</button>
    </div>
  </div>
</form>

<hr />

<h1>Individual Badge Printing</h1>
<?php
$this->setLayout('badge-search-form');
$this->page = 'badge-print';
echo $this->loadTemplate();
?>

<div id="status"></div>
