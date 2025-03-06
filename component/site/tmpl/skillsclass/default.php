<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */


defined('_JEXEC') or die;

/** @var \ClawCorpLib\Skills\Skill */
$class = $this->class;

?>

<div class="container">
  <div class="row align-items-center">
    <div class="col-12 col-lg-2 text-center">
      <a href="<?= $this->backLink ?>" role="button" class="btn btn-primary mb-2"><i class="fa fa-chevron-left"></i> Go Back</a>
    </div>
    <div class="col-12 col-lg-10">
      <div class="row">
        <h2 class="text-center"><?= $class->title ?></h2>
        <hr />
      </div>
      <div class="row">
        <div class="col text-center">
          <h3>
            <?php if (!is_null($class->day)):
              $time_slot = array_key_exists($class->time_slot, $this->time_slots) ? $this->time_slots[$class->time_slot] : 'TBA';

            ?>
              <?= $class->day->format('D') ?> <?= $time_slot ?>
            <?php endif ?>
          </h3>
        </div>
        <div class="col-12 text-center">Room: <?= $this->location ?? 'TBD' ?></div>
        <div class="col-12 p-1 m-2 text-center">Topic area: <?= $this->category ?></div>
      </div>
      <div class="row border border-warning p-2">
        <?= $class->description ?>
      </div>
      <?php if (trim($class->requirements_info)): ?>
        <div class="row border border-info p-2 mt-2">
          <p>Presenter requested prerequisites/requirements for attendees:<br />
            <?= $class->requirements_info ?> </p>
        </div>
      <?php endif; ?>
      <hr />
      <div class="row">
        <div class="col">
          <h2 class="mt-1 mb-2">
            <?= count($class->other_presenter_ids) > 1 ? 'Presenters' : 'Presenter' ?>
          </h2>
          <?php
          foreach ($this->presenters->keys() as $pid) {
          ?>
            <a href="<?= $this->presenters[$pid]->viewRoute() . '&cid=' . $class->id . '&tab=' . $this->urlTab ?>">
              <button type="button" class="btn btn-outline-light m-2"><?= $this->presenters[$pid]->name ?>&nbsp;<i class="fa fa-chevron-right"></i></button>
            </a>
          <?php
          }
          ?>
        </div>
      </div>

    </div>
  </div>
</div>
