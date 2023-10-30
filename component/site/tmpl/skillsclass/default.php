<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */


defined('_JEXEC') or die;

?>

<div class="container">
  <div class="row align-items-center">
    <div class="col-12 col-lg-2 text-center">
      <a href="<?= $this->backLink ?>" role="button" class="btn btn-primary mb-2"><i class="fa fa-chevron-left"></i> Go Back</a>
    </div>
    <div class="col-12 col-lg-10">
      <div class="row">
        <h2 class="text-center"><?= $this->class->title ?></h2>
        <hr />
      </div>
      <div class="row">
        <div class="col text-center">
          <h3><?= $this->class->day ?> <?= $this->class->time ?> (<?= $this->class->length ?> minutes)</h3>
        </div>
        <div class="col-12 text-center">Room: <?= $this->class->location ?></div>
        <div class="col-12 p-1 m-2 text-center">Topic area: <?= $this->class->category ?></div>
      </div>
      <div class="row border border-warning p-2 tight">
        <?= $this->class->description ?>
      </div>
      <hr />
      <div class="row">
        <div class="col">
          <h2 class="mt-1 mb-2">
            <?= count($this->class->presenters) > 1 ? 'Presenters' : 'Presenter' ?>
          </h2>
          <?php
          foreach ($this->class->presenters as $presenter) {
          ?>
            <a href="<?= $presenter->route . '&cid=' . $this->class->id . '&tab=' . $this->urlTab ?>">
              <button type="button" class="btn btn-outline-light m-2"><?= $presenter->name ?>&nbsp;<i class="fa fa-chevron-right"></i></button>
            </a>
          <?php
          }
          ?>
        </div>
      </div>

    </div>
  </div>
</div>