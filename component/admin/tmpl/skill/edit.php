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

$view = 'skill';

?>

<form action="<?php echo Route::_('index.php?option=com_claw&view='.$view.'&layout=edit&id=' . (int) $this->item->id); ?>"
	method="post" name="adminForm" id="<?php echo $view ?>-form" class="form-validate" enctype="multipart/form-data">

	<div class="row form-vertical mb-3">
    <div class="col-12 col-md-6">
        <?php echo $this->form->renderField('title'); ?>
    </div>
    <div class="col-12 col-md-6">
        <?php echo $this->form->renderField('presenters'); ?>
    </div>
	</div>
	<div class="row form-vertical mb-3">
		<div class="col-12 col-md-4">
        <?php echo $this->form->renderField('event'); ?>
    </div>
		<div class="col-12 col-md-4">
        <?php echo $this->form->renderField('location'); ?>
    </div>
    <div class="col-12 col-md-4">
        <?php echo $this->form->renderField('published'); ?>
    </div>
	</div>

	<div>
		<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'general')); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', 'General'); ?>

		<div class="row">
			<?php echo $this->form->renderField('day'); ?>
			<?php echo $this->form->renderField('start_time'); ?>
			<?php echo $this->form->renderField('length'); ?>
			<?php echo $this->form->renderField('audience'); ?>
			<?php echo $this->form->renderField('category'); ?>
			<?php echo $this->form->renderField('track'); ?>
			<?php echo $this->form->renderField('description'); ?>
		</div>

		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'special', 'Special'); ?>
		<div class="row">
			<?php echo $this->form->renderField('photo'); ?>
			<?php echo $this->form->renderField('arrival'); ?>
		</div>

		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php echo HTMLHelper::_('uitab.endTabSet'); ?>
	</div>

	<div class="mt-2">
		<?php echo $this->form->renderField('submission_date'); ?>
		<?php echo $this->form->renderField('mtime'); ?>
	</div> 
	
	<input type="hidden" name="id" value="<?php echo $this->item->id ?>"/>
	<input type="hidden" name="task" value=""/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
