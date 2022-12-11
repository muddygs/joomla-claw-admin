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

?>

<form action="<?php echo Route::_('index.php?option=com_claw&view=currentitem&layout=edit&id=' . (int) $this->item->id); ?>"
	method="post" name="adminForm" id="currentitem-form" class="form-validate">

	<div>
		<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'details')); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', 'Details'); ?>
		<div class="row">
			<?php echo $this->form->renderField('name'); ?>
			<?php echo $this->form->renderField('type'); ?>
			<?php echo $this->form->renderField('value'); ?>
			<?php echo $this->form->renderField('id'); ?>
		</div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php echo HTMLHelper::_('uitab.endTabSet'); ?>
	</div>
	
	<input type="hidden" name="task" value="">
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
