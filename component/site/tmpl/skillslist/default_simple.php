<?php
/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

if ( $this->list_type == 'simple' ) $this->tabId='';

foreach ( $this->list->tabs->overview['category'] AS $simple_item ) {
  if ( !count($simple_item['ids']) ) continue;

  ?>
    <h2><?= $simple_item['name'] ?></h2>

    <div class="table-responsive skills">
      <table class="table table-striped table-dark">
        <thead>
        <tr class="d-flex">
          <th class="col-6">Title</div>
          <th class="col-3">Day</div>
          <th class="col-3">Presenter(s)</div>
        </tr>
        </thead>
        <tbody>
  <?php

  foreach ( $simple_item['ids'] AS $classId ) {
    $class = $this->list->items[$classId];

    $url = '';

    $title = HTMLHelper::link(
      Route::_('index.php?option=com_claw&view=skillsclass&id=' . $classId) .'&tab='.$this->tabId,
      $class->track ?
        '<span class="badge rounded-pill text-bg-success">' . strtoupper($class->track) . '</span>&nbsp;' . $class->title :
        $class->title,
      ['class' => 'fs-5']
    );

    $day = $class->day_text;
    if (array_key_exists($class->time_slot, $this->time_slots)) {
      $timeSlot = $this->time_slots[$class->time_slot];
    } else {
      continue;
    }
  
    // Merge presenters
    $presenter_urls = [];
    $owner = true;

    foreach ($class->presenter_info as $presenter) {
      $link = HTMLHelper::link(
        Route::_('index.php?option=com_claw&view=skillspresenter&id=' . $presenter['uid']) . '&tab=' . $this->tabId,
        $presenter['name'],
        $owner ? ['class' => 'fs-5'] : ['class' => 'fw-light']
      );

      $presenter_urls[] = $link;
      $owner = false;
    }

    $presenter_links = implode('<br/>', $presenter_urls);

    ?>
      <tr class="d-flex">
      <td class="col-6"><?= $title ?></div>
      <td class="col-3"><?= $day ?> <?= $timeSlot ?></div>
      <td class="col-3"><?= $presenter_links ?></div>
      </tr>
    <?php

  }

  ?>
        </tbody>
      </table>
    </div>
  <?php
}

