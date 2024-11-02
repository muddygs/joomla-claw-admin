<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted Access');

use ClawCorpLib\Enums\PackageInfoTypes;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Button\PublishedButton;

$wa = $this->document->getWebAssetManager();
$wa->useScript('table.columns');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

$app = Factory::getApplication();
$user = $app->getIdentity();

$view = "packageinfos";

?>
<div class="container">
  <form action="<?php echo Route::_('index.php?option=com_claw&view=' . $view); ?>" method="post" name="adminForm" id="adminForm">
    <?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

    <div class="table-responsive">
      <table class="table table-striped table-bordered table-hover" id="<?= $view ?>List">
        <thead>
          <tr>
            <th class="w-1 text-center">
              <?php echo HTMLHelper::_('grid.checkall'); ?>
            </th>
            <th scope="col" class="w-1 text-center">
              <?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
            </th>
            <th scope="col">
              Type
            </th>
            <th scope="col">
              <?php echo HTMLHelper::_('searchtools.sort', 'Alias', 'a.alias', $listDirn, $listOrder); ?>
            </th>
            <th scope="col">
              <?php echo HTMLHelper::_('searchtools.sort', 'Title', 'a.title', $listDirn, $listOrder); ?>
            </th>
            <th scope="col">Start</th>
            <th scope="col">End</th>
            <th scope="col">ID</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($this->items as $i => $item) : ?>
            <tr class="row<?php echo $i % 2; ?>">
              <td class="text-center">
                <?php echo HTMLHelper::_('grid.id', $i, $item->id, false, 'cid', 'cb', $item->alias); ?>
              </td>

              <td class="article-status text-center">
                <?php
                $options = [
                  'task_prefix' => 'packageinfos.',
                  //'disabled' => $workflow_state || !$canChange,
                  'id' => 'published-' . $item->id
                ];

                echo (new PublishedButton)->render((int) $item->published, $i, $options);
                ?>
              </td>

              <td>
                <?= PackageInfoTypes::from($item->packageInfoType)->toString() ?>
              </td>
              <td>
                <?php
                if ($item->eventId != 0): ?>
                  <a href="<?php echo Route::_('index.php?option=com_eventbooking&view=event&id=' . $item->eventId); ?>" title="Edit in Event Booking" target="_blank">
                    <?= $item->alias . ' (' . $item->eventId . ')' ?>
                  </a>
                <?php
                elseif ($item->packageInfoType == PackageInfoTypes::coupononly->value):
                  echo 'N/A';
                else:
                  echo $item->alias;
                endif;
                ?>
              </td>

              <td>
                <a href="<?php echo Route::_('index.php?option=com_claw&task=packageinfo.edit&id=' . $item->id); ?>" title="Edit Package Info">
                  <?php echo $item->title ?>
                </a>
              </td>

              <td>
                <?php
                $start = new DateTime($item->start);
                echo $start->format('D g:i A');
                ?>
              </td>

              <td>
                <?php
                $end = new DateTime($item->end);
                echo $end->format('D g:i A');
                ?>
              </td>

              <td>
                <?= $item->id ?>
              </td>

            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="row">
      <?php echo $this->pagination->getListFooter(); ?>
    </div>

    <input type="hidden" name="task" value="">
    <input type="hidden" name="boxchecked" value="0">
    <?php echo HTMLHelper::_('form.token'); ?>

  </form>
</div>
