<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2025 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/** @var Joomla\CMS\Application\SiteApplication */
$app = Factory::getApplication();
/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $app->getDocument()->getWebAssetManager();
$wa->useScript('com_claw.jwtmon');
$wa->useScript('com_claw.meals');
$wa->useStyle('com_claw.admin');
$wa->useStyle('com_claw.checkin_css');
$wa->useScript('htmx');

/* @var \Joomla\CMS\Application\WebApplication */
$document = $app->getDocument();
$document->setMetaData('htmx-config', '{"responseHandling": [{"code":".*", "swap": true}]}');

?>
<div class="mb-2 p-1 bg-info text-white" id="jwtstatus"></div>
<h1 class="text-center">Meal Check In</h1>
<p>Please scan the badge or enter the Badge ID</p>


<form action="/php/checkin/mealsProcess.php" method="post" name="claw-process-checkin" id="claw-process-checkin" class="form-horizontal">
  <fieldset class="form-group">
    <div class="row">
      <legend class="col-form-label col-3">Meal Selection:</legend>
      <div class="col-9">
        <select name="mealEvent" id="mealEvent">
          <?php foreach ($this->meals as $categoryId => $info): ?>
            <option value="0" disabled><?= $info['title'] ?></option>
            <?php foreach ($info['packageIds'] as $eventid => $title): ?>
              <option value="<?= $eventid ?>">- <?= $title ?></option>
            <?php endforeach; ?>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
  </fieldset>

  <fieldset class="form-group">
    <div class="row">
      <legend class="col-form-label col-3">Scan Badge or Enter Badge #:</legend>
      <div class="col-3">
        <input name="badgecode" id="badgecode" value="" placeholder="" maxlength="255" size="15" class="" type="text"
          hx-post="/index.php?option=com_claw&task=mealcheckin.checkin&format=raw"
          hx-on::before-request='clearcode();'
          hx-trigger="keyup[key=='Enter']"
          hx-target="#status" />
      </div>
      <div class="col-6"></div>
    </div>
  </fieldset>

  <input type="hidden" name="token" id="token" value="<?= $this->token ?>" />
</form>

<div id="status"></div>

<div id="log"></div>
