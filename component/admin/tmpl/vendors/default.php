<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2022 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Button\PublishedButton;
use Joomla\CMS\Session\Session;

$wa = $this->document->getWebAssetManager();
$wa->useScript('table.columns');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

$canChange = true;
$saveOrder = true;

if ($saveOrder && !empty($this->items)) {
  $saveOrderingUrl = 'index.php?option=com_claw&task=vendors.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
  HTMLHelper::_('draggablelist.draggable');
}

$app = Factory::getApplication();
$user = $app->getIdentity();

?>
<div class="container">
  <div class="clearfix"></div>
  <form action="<?php echo Route::_('index.php?option=com_claw&view=vendors'); ?>" method="post" name="adminForm" id="adminForm">
    <?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

    <div class="table-responsive">
      <table class="table table-striped table-bordered table-hover" id="vendorsList">
        <thead>
          <tr>
            <th scope="col" style="width:1%" class="text-center d-none d-md-table-cell">
              <?php echo HTMLHelper::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
            </th>
            <th class="w-1 text-center">
              <?php echo HTMLHelper::_('grid.checkall'); ?>
            </th>
            <th scope="col" class="w-1 text-center">
              <?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
            </th>
            <th scope="col">
              <?php echo HTMLHelper::_('searchtools.sort', 'Name', 'a.name', $listDirn, $listOrder); ?>
            </th>
            <th scope="col">
              <?php echo HTMLHelper::_('searchtools.sort', 'Spaces', 'a.spaces', $listDirn, $listOrder); ?>
            </th>
            <th scope="col">Logo</th>
            <th scope="col">Mod Time</th>
            <th scope="col">ID</th>
          </tr>
        </thead>
        <tbody 
          <?php if ($saveOrder): ?>
            class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" data-nested="false"
          <?php endif; ?>
        />
          <?php foreach ($this->items as $i => $item) : ?>
            <tr class="row<?php echo $i % 2; ?>" data-draggable-group="0"
              data-item-id="<?=$item->id?>" data-parents="0"
              data-level="0">

              <td class="order text-center d-none d-md-table-cell">
              <?php
                $iconClass = '';
                if (!$canChange) {
                  $iconClass = ' inactive';
                } else if (!$saveOrder) {
                  $iconClass = ' inactive tip-top hasTooltip" title="' . HTMLHelper::_('tooltipText', 'JORDERINGDISABLED');
                }
              ?>

              <span class="sortable-handler<?php echo $iconClass; ?>">
                <span class="icon-menu" aria-hidden="true"></span>
              </span>

              <?php if ($canChange && $saveOrder) : ?>
                <input type="text" style="display:none" name="order[]" size="5"
                  value="<?=$item->ordering?>" class="width-20 text-area-order">
              <?php endif; ?>
            </td>

              <td class="text-center">
                <?php echo HTMLHelper::_('grid.id', $i, $item->id, false, 'cid', 'cb', $item->name); ?>
              </td>

              <td class="article-status text-center">
                <?php
                $options = [
                  'task_prefix' => 'vendors.',
                  //'disabled' => $workflow_state || !$canChange,
                  'id' => 'published-' . $item->id
                ];

                echo (new PublishedButton)->render((int) $item->published, $i, $options);
                ?>
              </td>

              <td>
                <a href="<?php echo Route::_('index.php?option=com_claw&task=vendor.edit&id=' . $item->id); ?>" title="Edit S&amp; Vendor">
                  <?php echo $item->name ?>
                </a>
              </td>

              <td><?= $item->spaces ?></td>

              <td>
                <?php 
                  if ( $item->logo !== '') {
                    $i = HTMLHelper::cleanImageURL($item->logo);

                    if (is_file(implode(DIRECTORY_SEPARATOR, [JPATH_ROOT, $i->url]))) {
                      ?>
                      <img src="/<?= $i->url ?>" style="max-width:100px; height:auto;" />
                      <?php
                    } else {
                      echo 'Invalid logo file';
                    }
                  } else {
                    echo 'No logo file';
                  }
                ?>
              </td>

              <td>
                <?php echo $item->mtime ?>
              </td>
              
              <td>
                <?php echo $item->id ?>
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