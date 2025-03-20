<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
/** @var \ClawCorpLib\Skills\Presenter */
$presenter = $this->presenter;

// Validate photo file exists
$photo = '';
if ($presenter->image_preview) {
  $photo = '<img src="/' . $presenter->image_preview . '" class="img-fluid rounded mx-auto"/>';
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
        <h2><?= $presenter->name ?></h2>
        <?= $presenter->bio ?>
        <?php if (trim($presenter->social_media)): ?>
          <p><?= $presenter->social_media ?></p>
        <?php endif; ?>
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
        <h2><?= $presenter->name ?></h2>
        <?= $presenter->bio ?>
        <?php if (trim($presenter->social_media)): ?>
          <p><?= $presenter->social_media ?></p>
        <?php endif; ?>
      </div>
    </div>
  <?php
  endif;
  ?>
  <div class="row mt-3">
    <div class="col">
      <a href="<?= $this->backLink ?>" role="button" class="btn btn-primary mb-2"><i class="fa fa-chevron-left"></i> Go Back</a>
    </div>
  </div>
</div>
