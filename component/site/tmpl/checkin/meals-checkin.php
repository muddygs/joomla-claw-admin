<?php

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Lib\Jwtwrapper;

Jwtwrapper::redirectOnInvalidToken('meals-checkin', $this->token);

/** @var Joomla\CMS\Application\SiteApplication */
$app = Factory::getApplication();
/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $app->getDocument()->getWebAssetManager();
$wa->useScript('com_claw.jwtmon');
$wa->useScript('com_claw.meals');
$wa->useStyle('com_claw.admin');

?>
<div class="mb-2 p-1 bg-info text-white" id="jwtstatus"></div>
<h1>Meal Check In</h1>
<p>Please scan the badge or enter the Badge ID</p>


<form action="/php/checkin/mealsProcess.php" method="post" name="claw-process-checkin" id="claw-process-checkin" class="form-horizontal">
	<fieldset class="form-group">
		<div class="row">
			<legend class="col-form-label col-3">Meal Selection:</legend>
			<div class="col-9">
				<select name="mealEvent" id="mealEvent">
					<?php foreach ( $this->meals AS $id => $d ): ?>
					<option value="<?php echo $id < 0 ? 0 : $id ?>"<?php if ($id<0) echo ' disabled'?>><?php echo $d ?></option>
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
          onclick="clearcode();" onchange="doMealCheckin();"/>
			</div>
			<div class="col-6"></div>
		</div>
	</fieldset>

	<input type="hidden" id="registration_code" value="" />

	<input type="hidden" name="token" id="token" value="<?php echo $this->token ?>" />
</form>

<div id="status"></div>