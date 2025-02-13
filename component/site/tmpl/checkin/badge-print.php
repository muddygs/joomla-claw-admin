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

use Joomla\CMS\Factory;

/** @var Joomla\CMS\Application\SiteApplication */
$app = Factory::getApplication();

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $app->getDocument()->getWebAssetManager();
$wa->useScript('com_claw.jwtmon');
$wa->useScript('com_claw.checkin');
$wa->useStyle('com_claw.admin');
$wa->useScript('htmx');

$this->page = 'badge-print';
$this->placeholder = true;

?>
<div class="mb-2 p-1 text-bg-info text-end" id="jwtstatus"></div>
<h1 class="text-center">Batch Printing</h1>

<form method="post" name="claw-process-badge-batch" id="claw-process-badge-batch">
  <div id="badgeCounts" hx-post="index.php?option=com_claw&task=checkin.count&format=raw" hx-trigger="load delay:5s" hx-swap="outerHTML">
    <?php
    $this->setLayout('htmx_badge_count');
    echo $this->loadTemplate();
    ?>
  </div>
  <div class="row">
    <div class="col-4">
      <label for="batchcount1" class="form-label">Enter # of Badges to Print (1-50)</label>
      <input type="number" name="batchcount1" id="batchcount1" min="1" max="50" step="1" value="10" />
      <button type="button" class="btn btn-primary" id="submitBatch" onclick="doBatchPrint(1);">Generate Badges</button>
    </div>
    <div class="col-4">
      <label for="batchcount2" class="form-label">Enter # of Badges to Print (1-50)</label>
      <input type="number" name="batchcount2" id="batchcount2" min="1" max="50" step="1" value="10" />
      <button type="button" class="btn btn-primary" id="submitBatch" onclick="doBatchPrint(2);">Generate Badges</button>
    </div>
    <div class="col-4">
      <label for="batchcount0" class="form-label">Enter # of Badges to Print (1-50)</label>
      <input type="number" name="batchcount0" id="batchcount0" min="1" max="50" step="1" value="10" />
      <button type="button" class="btn btn-primary" id="submitBatch" onclick="doBatchPrint(0);">Generate Badges</button>

    </div>
    <input type="hidden" id="counttoken" name="counttoken" value="<?= $this->token ?>" />
</form>

<hr class="mt-3" />

<h1 class="text-center">Individual Badge Printing</h1>
<?php
$this->setLayout('badge-search-form');
echo $this->loadTemplate();
?>

<div id="status"></div>

<?php if ($this->page == 'badge-print'): ?>
  <div class="form-group" id="form-print-buttons">
    <div class="row">
      <div class="col">
        <input name="submitPrint" id="submitPrint" type="button" value="Print Badge" class="btn btn-danger mb-2 w-100" onclick="doPrint();" />
      </div>
      <div class="col">
        <input name="submitPrintIssue" id="submitPrintIssue" type="button" value="Issue + Print Badge" class="btn btn-info mb-2 w-100" onclick="doPrint(true);" />
      </div>
    </div>
  </div>
<?php endif; ?>
