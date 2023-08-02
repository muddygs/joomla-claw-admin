<?php
/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$backurl = '';

if (array_key_exists('tab', $_GET)) {
  $tab = $_GET['tab'];
  if (strpos($tab, 'fri') === 0 || strpos($tab, 'sat') === 0 || strpos($tab, 'sun') === 0) {
    $backurl = $_SERVER['HTTP_REFERER'] . '#' . $tab;
  }
}

if ($backurl == '') {
  $click = 'history.back(-1)';
} else {
  $click = "document.location='$backurl'";
}

// TODO: Return to home
if ($this->presenter == null) {
  echo "no presenter";
  return;
}


// Validate photo file exists
$photo = '';
if ($this->presenter->photo ) {
  if (is_file(implode(DIRECTORY_SEPARATOR, [JPATH_ROOT, $this->presenter->photo]))) {
    $photo = '<img src="' . $this->presenter->photo. '" class="img-fluid rounded mx-auto"/>';
  }
}

?>

<div class="container">

  <?php
    if (!$photo) :
  ?>
      <div class="row">
        <div class="col">
          <h2 style="text-align:center;">Skills & Education Presenter</h2>
          <hr>
        </div>
      </div>
      <div class="row">
        <div class="col">
          <h2><?= $this->presenter->name ?></h2>
          <?= $this->presenter->bio ?>
        </div>
      </div>
    <?php
    else :
    ?>
      <div class="row">
        <div class="col">
          <h2 style="text-align:center;">Skills & Education Presenter</h2>
          <hr>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12 col-lg-3"><?= $photo ?></div>
        <div class="w-100 d-lg-none"></div>
        <div class="col-md-12 col-lg-9">
          <h2><?= $this->presenter->name ?></h2>
          <?= $this->presenter->bio ?>
        </div>
      </div>
  <?php
    endif;
  ?>
  <div class="row mt-3">
    <div class="col">
      <button type="button" class="btn btn-primary" onClick="<?php echo $click ?>"><i class="fa fa-chevron-left"></i> Go Back</button>
    </div>
  </div>
</div>