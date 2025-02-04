<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;

/** @var Joomla\CMS\Application\AdministratorApplication */
$app = Factory::getApplication();
/** @var Joomla\CMS\WebAsset\WebAssetManager */
$wa = $app->getDocument()->getWebAssetManager();
$wa->useScript('com_claw.coupongenerator');
$wa->useScript('htmx');

$token = Session::getFormToken();

?>

<h1>Coupon Generator</h1>

<form
  action="#"
  method="post"
  name="Coupon Generator"
  id="claw-coupon-generator"
  class="row g-3"
  hx-headers='{"X-CSRF-Token": "<?= $token ?>"}'>
  <div class="col-12">
    <p>Enter quantity and select coupon registration type and any add ons.</p>
  </div>

  <div class="col-12">
    <?php echo $this->form->renderField('event'); ?>
  </div>

  <div class="col-12">
    <?php echo $this->form->renderField('quantity'); ?>
  </div>


  <div class="row">
    <div class="col-12 col-lg-4">
      <?php echo $this->form->renderField('packageid'); ?>
    </div>

    <div class="col-12 col-lg-4">
      <?php echo $this->form->renderField('addons'); ?>
    </div>

    <div class="col-12 col-lg-4">
      <label id="jform_value-lbl" for="jform_value">Total Value</label>
      <div id="total_value"
        hx-post="/administrator/index.php?option=com_claw&task=coupongenerator.couponValue&format=raw"
        hx-trigger="change from:#jform_event delay:1s, change from:#jform_packageid delay:1s, change from:#jform_addons">
        $0.00
      </div>
    </div>
  </div>

  <?= $this->form->renderField('owner-fields') ?>

  <div class="text-end">
    <input name="checkEmail" id="checkEmail" type="button" value="Validate Email(s)" class="btn btn-info w-50"
      hx-post="/administrator/index.php?option=com_claw&task=coupongenerator.emailStatus&format=raw"
      hx-target="#emailstatus" />
    <?php if ($this->emailOverride) : ?>
      <div class="ms-2 form-check float-end">
        <input class="form-check-input" type="checkbox" value="1" id="emailOverride" name="emailOverride">
        <label class="form-check-label" for="emailOverride">Ignore email errors</label>
      </div>
    <?php endif; ?>
  </div>
  <div id="emailstatus" class="text-info text-end"></div>

  <hr />

  <div class="row">
    <div class="col-6">
      <input name="generate" id="generate" type="button" value="Generate" class="btn btn-danger mb-2" hx-post="/administrator/index.php?option=com_claw&task=coupongenerator.createCoupons&format=raw" hx-target="#results" />
    </div>
    <div class="col-6 text-end">
      <button name="copy" id="copy" class="btn btn-info mb-2" onclick="copyCoupons()"
        data-bs-toggle="tooltip" data-bs-placement="top" title="Copy to Clipboard">
        <span class="fa fa-copy"></span>
      </button>
    </div>

    <?php echo HTMLHelper::_('form.token'); ?>
</form>


<div id="results">
  <p>Ready!</p>
</div>
