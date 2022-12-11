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

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

$app = Factory::getApplication();
$user = $app->getIdentity();

?>
<div class="container">
<form action="<?php echo Route::_('index.php?option=com_claw&view=currentitems'); ?>" method="post" name="adminForm" id="adminForm">
  <?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

  <div class="table-responsive">
    <table class="table table-striped table-bordered">
    <thead>
      <tr>
        <th class="w-1 text-center">
			    <?php echo HTMLHelper::_('grid.checkall'); ?>
		    </th>
		    <th scope="col">
			    <?php echo HTMLHelper::_('searchtools.sort', 'Key', 'a.name', $listDirn, $listOrder); ?>
		    </th>
		    <th scope="col">Type</th>
		    <th scope="col">Value(s)</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ( $this->items AS $i => $item ): 
      // JSON decode value
        $valueDecode = json_decode($item->value, true);
        $values = [];

        if ( $valueDecode != null )
        {
          $values = array_column($valueDecode, 'subvalue');
        }

        $value = implode('<br/>', $values);
  
      ?>
        <tr>
          <td class="text-center">
            <?php echo HTMLHelper::_('grid.id', $i, $item->id, false, 'cid', 'cb', $item->name); ?>
          </td>
          <td>
            <a href="<?php echo Route::_('index.php?option=com_claw&task=currentitem.edit&id=' . $item->id); ?>"
      			  title="Edit <?php echo $this->escape($item->name); ?>">
              <?php echo $item->name ?>
            </a>
          </td>
          <td>
            <?php echo $item->type ?>
          </td>
          <td>
            <?php echo $value ?>
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
