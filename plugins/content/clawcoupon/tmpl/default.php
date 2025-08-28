<?php

\defined('_JEXEC') or die;

use ClawCorpLib\Helpers\EventBooking;
use ClawCorpLib\Helpers\Helpers;

Helpers::sessionSet('clawcoupon', '');
?>
<img src="images/<?= strtolower($this->eventConfig->eventInfo->prefix) ?>/banners/registration.svg" class="img-fluid mx-auto d-block mb-3" alt="Registration Banner" title="Registration Banner" />

<?php
if ($this->referrer != '') {
  $refImagePath = JPATH_ROOT . '/images/0_static_graphics/referrers/' . $this->referrer;
  $files = glob($refImagePath . ".{[jJ][pP][gG],[pP][nN][gG],[sS][vV][gG]}", GLOB_BRACE);
  if (count($files)) {
    $file = substr($files[0], strlen(JPATH_ROOT));
?>
    <img alt="<?= $this->referrer ?> Banner" title="<?= $this->referrer ?> Banner" src="<?= $file ?>" class="img-fluid mx-auto d-block mb-3" />
<?php
  }
}
?>

<?php
if (!is_null($this->mainEvent)):
?>
  <h1 class="text-center">You are already registered</h1>
  <div class="d-grid gap-2 col-6 mx-auto mb-3">
    <a href="/account/my-reg" role="button" class="btn btn-success btn-lg btn-large">View Registrations / Get Addons</a>
  </div>
  <p class="text-center"><strong>If you are trying to register another person, please <a href="index.php?option=com_users&view=login&layout=logout&task=user.menulogout" class="text-decoration-underline">SIGN OUT</a> and start again using that person's account.</strong></p>
<?php
  return;
endif;

?>
<form action="/php/pages/registrationsurvey.php" method="post" name="Coupon Validator" id="registration-survey-coupon" class="row">
  <?php

  if (0 == $this->autoCoupon->eventId):
  ?>
    <h1 class="rstpl-title-left text-white">Have a coupon?</h1>
  <?php
  else:
    $databaseRow = EventBooking::loadEventRow($this->autoCoupon->eventId);
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
      For assistance, contact <a href="/help?category_id=17" style="color:var(--claw-warning)">Guest Services</a>.
    <?php
    endif;
    ?>
  </div>
</form>
