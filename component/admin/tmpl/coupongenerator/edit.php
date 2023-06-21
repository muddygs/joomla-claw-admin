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
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Form\Form;

use ClawCorpLib\Helpers\Helper;
use Joomla\CMS\Factory;

/** @var Joomla\CMS\Application\AdministratorApplication */
$app = Factory::getApplication();
/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $app->getDocument()->getWebAssetManager();
$wa->useScript('com_claw.coupongenerator');

// $avl = Helpers::getUserViewLevelsByName();

// if (!array_key_exists('Discount Codes', $avl)) {
//   die("Restricted access");
// }

$allowOverride = array_key_exists('Special', $avl ?? []) ? true: false;

?>
<h1>Coupon Generator</h1>

<form action="/php/coupons/coupons.php" method="post" name="Coupon Generator" id="claw-coupon-generator" class="row g-3">
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
    <div class="col-4">
      <?php echo $this->form->renderField('packagetype'); ?>
    </div>

    <div class="col-4">
      <?php echo $this->form->renderField('addons'); ?>
    </div>

    <div class="col-4">
      <?php echo $this->form->renderField('value'); ?>
    </div>
  </div>

  <div class="row">
    <div class="col-6">
      <?php echo $this->form->renderField('name'); ?>
    </div>

    <div class="col-6">
      <?php echo $this->form->renderField('email'); ?>
      <div id="emailstatus" class="text-info"></div>
      <?php if ($allowOverride): ?>
      <div class="form-check">
        <input class="form-check-input" type="checkbox" value="1" id="emailOverride" name="emailOverride">
        <label class="form-check-label" for="emailOverride">Ignore email</label>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="col-12">
  <input name="submit" id="submit" type="button" value="Generate" class="btn btn-danger mb-2" onclick="/>
  </div>
</form>


<button name="copy" id="copy" class="btn btn-info mb-2" onclick="copyCoupons()">Copy Coupons Codes to Clipboard</button>

<div id="results">
  <p>Ready!</p>
</div>