<?php
/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use ClawCorpLib\Helpers\Helpers;
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
    $url = '';
    $presenter_urls = [];

    $title = HTMLHelper::link(
      Route::_('index.php?option=com_claw&view=skillsclass&id=' . $classId) .'&tab='.$this->tabId,
      $this->list->items[$classId]->title
    );

    $day = $this->list->items[$classId]->day_text;
    $timeSlot = explode(':', $this->list->items[$classId]->time_slot, 2);
    $startTime = Helpers::formatTime(substr($timeSlot[0], 0, 2).':'.substr($timeSlot[0], 2, 2));

    foreach ( $this->list->items[$classId]->presenter_info AS $presenter ) {
      $presenter_urls[] = HTMLHelper::link(
        Route::_('index.php?option=com_claw&view=skillspresenter&id=' . $presenter['uid']),
        $presenter['name']
      );
    }

    ?>
      <tr class="d-flex">
      <td class="col-6"><?= $title ?></div>
      <td class="col-3"><?= $day ?> <?= $startTime ?></div>
      <td class="col-3"><?php echo implode('<br/>',$presenter_urls) ?></div>
      </tr>
    <?php

  }

  ?>
        </tbody>
      </table>
    </div>
  <?php
}

