<?php

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Lib\ClawEvents;

Helpers::sessionSet('clawcoupon', '');

/** @var \ClawCorp\Component\Claw\Site\View\Registrationsurvey\HtmlView $this */

/** @var Joomla\CMS\Application\SiteApplication */
$app = Factory::getApplication();
/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $app->getDocument()->getWebAssetManager();
$wa->useScript('com_claw.registrationsurvey');
?>
<div style="text-align: center;">
  <p><img alt="Registration Banner" src="images/<?= strtolower($this->prefix) ?>/banners/registration.svg" class="img-fluid mx-auto d-block" alt="Registration Banner" title="Registration Banner" /></p>
</div>

<?php
$ref = Helpers::sessionGet('referrer');
if ($ref != '') {
  $refImagePath = JPATH_ROOT . '/images/0_static_graphics/referrers/' . $ref;
  $files = glob($refImagePath . ".{[jJ][pP][gG],[pP][nN][gG],[sS][vV][gG]}", GLOB_BRACE);
  if (count($files)) {
    $file = substr($files[0], strlen(JPATH_ROOT));
?>
    <img alt="<?= $ref ?> Banner" title="<?= $ref ?> Banner" src="<?= $file ?>" class="img-fluid mx-auto mb-3" />
<?php
  }
}
?>

<div class="border border-2 border-info rounded mb-5">
  <h3 class="m-2 text-center">One Registration Per Person. Any addons must be purchased <u>per registration</u>.</h3>
</div>

<?php
if (!is_null($this->mainEvent)):
?>
  <h1 class="text-center">You are already registered</h1>
  <div class="d-grid gap-2 col-6 mx-auto mb-3">
    <a href="/planning/my-reg" role="button" class="btn btn-danger">View Registrations</a>
    <a href="<?= $this->registrationLinks['addons'] ?>" role="button" class="btn btn-success">Get Addons/Shifts</a>
  </div>
  <p>If you are trying to register another person, please SIGN OUT (under the Registration menu) and start again using that person's account.</p>
<?php
  return;
endif;

if ($this->onsiteActive):
?>
  <h1>Already Registered?</h1>
  <div class="d-grid mb-3">
    <a href="<?= $this->registrationLinks['addons'] ?>" class="btn btn-success btn-lg" role="button">
      Get Addons/Shifts
    </a>
  </div>
<?php
endif;

?>
<form action="/php/pages/registrationsurvey.php" method="post" name="Coupon Validator" id="registration-survey-coupon" class="row">
  <?php

  if (0 == $this->autoCoupon->eventId):
  ?>
    <h1>Have a coupon?</h1>
  <?php
  else:
    $databaseRow = ClawEvents::loadEventRow($this->autoCoupon->eventId);
  ?>
    <h1>You have a coupon assigned to your account</h1>
    <p>Coupon Event Assignment: <strong><?= $databaseRow->title ?></strong></p>
    <ul>
      <li>To request a different coupon type, contact <a href="/help?category_id=17">Guest Services</a>.</li>
      <li>If you have a different coupon, you may enter it below instead.</li>
      <li>If you do not wish to use this coupon, please select a registration type below.</li>
    </ul>
  <?php
  endif;
  ?>
  <label for="coupon" class="form-label">Enter your coupon below and click the START REGISTRATION button. Coupons are not
    case sensitive.
  </label>
  <div class="input-group mb-3">
    <input type="text" class="form-control" name="coupon" value="<?= $this->autoCoupon->code ?>" id="coupon" placeholder="?-ABCD-EFGH" aria-label="Coupon Entry FIeld">
    <button class="btn btn-danger" type="button" onclick="validateCoupon()">START REGISTRATION</button>
  </div>
  <div id="couponerror" class="bg-danger text-light rounded-2 d-none">
    That coupon is not valid. Please verify your entry.
    <?php
    if ($this->onsiteActive):
    ?>
      Go to the registration help desk for assistance.
    <?php
    else:
    ?>
      For assistance, contact <a href="/help?category_id=17" style="color:#ffae00">Guest Services</a>.
    <?php
    endif;
    ?>
  </div>
</form>
