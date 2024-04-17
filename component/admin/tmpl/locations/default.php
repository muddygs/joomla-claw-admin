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

$wa = $this->document->getWebAssetManager();
$wa->useScript('table.columns');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

?>
<div class="container">
  <form action="<?php echo Route::_('index.php?option=com_claw&view=locations'); ?>" method="post" name="adminForm" id="adminForm">
    <?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

    <div class="table-responsive">
      <table class="table table-striped table-bordered" id="locationsList">
        <thead>
          <tr>
            <th class="w-1 text-center">
              <?php echo HTMLHelper::_('grid.checkall'); ?>
            </th>
            <th scope="col">
              <?php echo HTMLHelper::_('searchtools.sort', 'Value', 'a.value', $listDirn, $listOrder); ?>
            </th>
            <th scope="col">ID</th>
          </tr>
        </thead>
        <tbody />
        <?php foreach ($this->items as $i => $item) : ?>
          <tr class="row<?php echo $i % 2; ?>">
            <td class="text-center">
              <?php echo HTMLHelper::_('grid.id', $i, $item->id, false, 'cid', 'cb', $item->id); ?>
            </td>

            <td>
              <a href="<?php echo Route::_('index.php?option=com_claw&task=location.edit&id=' . $item->id); ?>" title="Edit <?php echo $this->escape($item->value); ?>">
                <?= $item->value ?>
              </a>
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