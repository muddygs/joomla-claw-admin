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
use Joomla\CMS\Session\Session;

// Ideas on making a drag-n-drop list
// https://joomla.stackexchange.com/questions/14374/adding-drag-n-drop-ordering-in-component
// Newer?
// https://blog.astrid-guenther.de/en/joomla-filtern-sortieren-suchen/
// https://docs.joomla.org/J3.x:Developing_an_MVC_Component/Adding_Ordering

$wa = $this->document->getWebAssetManager();
$wa->useScript('table.columns');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

$saveOrder = $listOrder == 'a.ordering';
$canChange = true;

if ($saveOrder && !empty($this->items))
{
    $saveOrderingUrl = 'index.php?option=com_claw&task=locations.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
    HTMLHelper::_('draggablelist.draggable');
  }

$app = Factory::getApplication();
$user = $app->getIdentity();

?>
<div class="container">
<form action="<?php echo Route::_('index.php?option=com_claw&view=locations'); ?>" method="post" name="adminForm" id="adminForm">
  <?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

  <div class="table-responsive">
    <table class="table table-striped table-bordered" id="locationsList">
    <thead>
      <tr>
        <th scope="col" style="width:1%" class="text-center d-none d-md-table-cell">
          <?php echo HTMLHelper::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
        </th>
        <th class="w-1 text-center">
			    <?php echo HTMLHelper::_('grid.checkall'); ?>
		    </th>
		    <th scope="col">
			    <?php echo HTMLHelper::_('searchtools.sort', 'Value', 'a.value', $listDirn, $listOrder); ?>
		    </th>
		    <th scope="col">Alias</th>
        <th scope="col">ID</th>
      </tr>
    </thead>
    <tbody <?php if ($saveOrder):
      ?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" data-nested="true"<?php
      endif; ?>>
      <?php foreach ( $this->items AS $i => $item ): 
        // Get the parent of item for sorting
        if ($item->parent_id > 0) {
          $parentsStr       = ' ' . $item->parent_id;
          $itemLevel = 1;
        } else {
            $parentsStr = '';
            $itemLevel = 0;
        }
    
      ?>
        <tr class="row<?php echo $i % 2; ?>" data-draggable-group="<?=$item->parent_id?>"
          data-item-id="<?=$item->id?>" data-parents="<?=$parentsStr?>"
          data-level="<?=$itemLevel?>">
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
            <?php echo HTMLHelper::_('grid.id', $i, $item->id, false, 'cid', 'cb', $item->value); ?>
          </td>
          <td>
            <a href="<?php echo Route::_('index.php?option=com_claw&task=location.edit&id=' . $item->id); ?>"
      			  title="Edit <?php echo $this->escape($item->value); ?>">
              <?=$item->treename?>
            </a>
          </td>
          <td>
            <?=$item->alias?>
          </td>
          <td>
            <?=$item->id?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
    </table>
  </div>

  <input type="hidden" name="task" value="">
  <input type="hidden" name="boxchecked" value="0">
  <?php echo HTMLHelper::_('form.token'); ?>

</form>
</div>
