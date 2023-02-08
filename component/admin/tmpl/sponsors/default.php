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

use ClawCorpLib\Helpers\SponsorshipType;

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

$app = Factory::getApplication();
$user = $app->getIdentity();

?>
<div class="container">
<form action="<?php echo Route::_('index.php?option=com_claw&view=sponsors'); ?>" method="post" name="adminForm" id="adminForm">
  <?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

  <div class="table-responsive">
    <table class="table table-striped table-bordered">
    <thead>
      <tr>
        <th class="w-1 text-center">
			    <?php echo HTMLHelper::_('grid.checkall'); ?>
		    </th>
        <th scope="col">
          Published
        </th>
		    <th scope="col">
			    <?php echo HTMLHelper::_('searchtools.sort', 'Sponsor Name', 'a.name', $listDirn, $listOrder); ?>
		    </th>
		    <th scope="col">Type</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ( $this->items AS $i => $item ): 
      ?>
        <tr>
          <td class="text-center">
            <?php echo HTMLHelper::_('grid.id', $i, $item->id, false, 'cid', 'cb', $item->name); ?>
          </td>
          <td class="article-status text-center">
            <?php
              $options = [
                'task_prefix' => 'sponsors.',
                //'disabled' => $workflow_state || !$canChange,
                'id' => 'state-' . $item->id
              ];

              echo (new PublishedButton)->render((int) $item->published, $i, $options);
            ?>
          </td>

          <td>
            <a href="<?php echo Route::_('index.php?option=com_claw&task=sponsor.edit&id=' . $item->id); ?>"
      			  title="Edit <?php echo $this->escape($item->name); ?>">
              <?php echo $item->name ?>
            </a>
          </td>
          <td>
            <?php 
              echo SponsorshipType::from($item->type)->toString();
            ?>
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
