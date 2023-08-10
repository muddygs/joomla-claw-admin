<?php
/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2022 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;

/** @var Joomla\CMS\Application\AdministratorApplication */
$app = Factory::getApplication();
/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $app->getDocument()->getWebAssetManager();
$wa->useScript('com_claw.refunds');

?>
<h1>Payments Maintenance</h1>
<p>Enter <i>any</i> invoice number associated with the registrant to manage. Only the last 365 days shown.</p>

<form name="Refunds" id="claw-refund" class="row g-3">
	<div class="row">
		<div class="col-6">
      <?php echo $this->form->renderField('invoice'); ?>
			<button name="populate" id="populate" type="button" class="btn btn-danger" onclick="doPopulate();">Look up</button>
		<div class="col-6"></div>
	</div>

	<div class="mb-4" id="events">
		No events available.
	</div>

	<div class="row">
		<div class="col-12 col-lg-6">
      <?php echo $this->form->renderField('refundSelect'); ?>
      <?php echo $this->form->renderField('refundAmount'); ?>
      <?php echo $this->form->renderField('cancelall'); ?>

      <div>
        <input name="refundSubmit" id="refundSubmit" type="button" value="Submit Refund" class="btn btn-danger mb-2" onclick="processRefund()" disabled />
			</div>
		</div>
		<div class="col-12 col-lg-6">
      <?php echo $this->form->renderField('profileSelect'); ?>
      <?php echo $this->form->renderField('profileAmount'); ?>
      <?php echo $this->form->renderField('profileDescription'); ?>

			<div>
				<input name="profileSubmit" id="profileSubmit" type="button" value="Charge Profile" class="btn btn-danger mb-2" onclick="processProfile()" disabled />
			</div>

		</div>
	</div>

	<div class="form-group" id="form-row-refundSubmit">
	</div>

	<div class="text-monospace col-12 bg-white text-dark" id="results"></div>
  <?php echo HTMLHelper::_('form.token'); ?>
</form>

