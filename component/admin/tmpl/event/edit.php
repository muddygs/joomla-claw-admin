<?php
/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2022 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Form\Form;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

$view = 'event';

?>

<form action="<?php echo Route::_('index.php?option=com_claw&view='.$view.'&layout=edit&id=' . (int) $this->item->id); ?>"
	method="post" name="adminForm" id="<?php echo $view ?>-form" class="form-validate">

	<div class="row form-vertical mb-3">
    <div class="col-12 col-md-8">
        <?php echo $this->form->renderField('event_title'); ?>
    </div>
    <div class="col-12 col-md-4">
        <?php echo $this->form->renderField('published'); ?>
    </div>
	</div>

	<div>
		<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'details')); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', 'General'); ?>
		<div class="row">
			<?php echo $this->form->renderField('day'); ?>
			<?php echo $this->form->renderField('start_time'); ?>
			<?php echo $this->form->renderField('end_time'); ?>
			<?php echo $this->form->renderField('featured'); ?>
			<?php echo $this->form->renderField('location'); ?>
			<?php echo $this->form->renderField('event_description'); ?>
			<?php echo $this->form->renderField('sponsors'); ?>


		</div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'special', 'Special'); ?>
			<?php echo $this->form->renderField('fee_event'); ?>
			<?php echo $this->form->renderField('event_id'); ?>
			<?php echo $this->form->renderField('onsite_description'); ?>
			<?php echo $this->form->renderField('poster'); ?>


		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php echo HTMLHelper::_('uitab.endTabSet'); ?>
	</div>
	
	<input type="hidden" name="id" value="<?php echo $this->item->id ?>"/>
	<input type="hidden" name="sort_order" value="<?php echo $this->item->sort_order ?>"/>
	<input type="hidden" name="task" value=""/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
