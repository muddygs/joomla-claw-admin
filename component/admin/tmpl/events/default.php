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

$wa = $this->document->getWebAssetManager();
$wa->useScript('table.columns');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

$app = Factory::getApplication();
$user = $app->getIdentity();

?>
<div class="container">
<form action="<?php echo Route::_('index.php?option=com_claw&view=events'); ?>" method="post" name="adminForm" id="adminForm">
  <?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

  <div class="table-responsive">
    <table class="table table-striped table-bordered" id="eventsList">
    <thead>
      <tr>
        <th class="w-1 text-center">
			    <?php echo HTMLHelper::_('grid.checkall'); ?>
		    </th>
		    <th scope="col">
			    <?php echo HTMLHelper::_('searchtools.sort', 'Day', 'a.day', $listDirn, $listOrder); ?>
		    </th>
		    <th scope="col">
			    <?php echo HTMLHelper::_('searchtools.sort', 'Start Time', 'a.start_time', $listDirn, $listOrder); ?>
		    </th>
		    <th scope="col">Title</th>
        <th scope="col">Location</th>
        <th scole="col">Sponsors</th>
        <th scope="col">ID</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ( $this->items AS $i => $item ): ?>
        <tr class="row<?php echo $i % 2; ?>">
          <td class="text-center">
            <?php echo HTMLHelper::_('grid.id', $i, $item->id, false, 'cid', 'cb', $item->event_title); ?>
          </td>
          <td>
            <?php echo $item->day_text ?>
          </td>
          <td>
            <?php echo $item->start_time_text ?>
          </td>
          <td>
            <a href="<?php echo Route::_('index.php?option=com_claw&task=event.edit&id=' . $item->id); ?>"
      			  title="Edit Event">
              <?php echo $item->event_title ?>
            </a>
          </td>
          <td>
              <?php echo $item->location_text ?>
          </td>
          <td>
            TODO: Sponsor list
          </td>
          <td>
            <?php echo $item->id ?>
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
