<?php
/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;

foreach ( $this->list->tabs->overview['category'] AS $simple_item )
{
  if ( !count($simple_item['ids']) ) continue;

  ?>
    <h2><?= $simple_item['name'] ?></h2>

    <div class="table-responsive">
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

    $title = $this->list->items[$classId]->title;
    $day = $this->list->items[$classId]->day_text;

    foreach ( $this->list->items[$classId]->presenter_info AS $presenter ) {
      $presenter_urls[] = '<a href="' . Route::_('index.php?option=com_claw&view=skillspresenter&id=' . $presenter['uid']) . '">' . $presenter['name'] . '</a>';
    }

    ?>
      <tr class="d-flex">
      <td class="col-6"><?= $title ?>&nbsp;<i class="fa fa-chevron-right"></i></div>
      <td class="col-3"><?= $day ?></div>
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
