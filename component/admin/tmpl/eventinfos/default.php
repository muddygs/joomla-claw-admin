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

use ClawCorpLib\Helpers\EventBooking;
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

$view = "eventinfos";

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
              <?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.active', $listDirn, $listOrder); ?>
            </th>
            <th scope="col">
              <?php echo HTMLHelper::_('searchtools.sort', 'Alias', 'a.alias', $listDirn, $listOrder); ?>
            </th>
            <th scope="col">
              <?php echo HTMLHelper::_('searchtools.sort', 'Description', 'a.description', $listDirn, $listOrder); ?>
            </th>
            <th scope="col">Start</th>
            <th scope="col">End</th>
            <th scope="col">Location</th>
            <th scope="col">ID</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($this->items as $i => $item) : ?>
            <tr>
              <td class="text-center">
                <?php echo HTMLHelper::_('grid.id', $i, $item->id, false, 'cid', 'cb', $item->alias); ?>
              </td>

              <td class="article-status text-center">
                <?php
                $options = [
                  'task_prefix' => 'eventinfos.',
                  //'disabled' => $workflow_state || !$canChange,
                  'id' => 'published-' . $item->id
                ];

                echo (new PublishedButton)->render((int) $item->active, $i, $options);
                ?>
              </td>

              <td>
                <a href="<?php echo Route::_('index.php?option=com_claw&task=eventinfo.edit&id=' . $item->id); ?>" title="Edit Event Info">
                  <?php echo $item->alias ?>
                </a>
              </td>

              <td>
                <a href="<?php echo Route::_('index.php?option=com_claw&task=eventinfo.edit&id=' . $item->id); ?>" title="Edit Event Info">
                  <?php echo $item->description ?>
                </a>
              </td>

              <td>
                <?= $item->start_date ?>
              </td>

              <td>
                <?= $item->end_date ?>
              </td>

              <td>
                <?= EventBooking::getLocationName($item->ebLocationId) ?>
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
