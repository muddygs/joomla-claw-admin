<?php
/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

$view = 'location';
?>

<form action="<?php echo Route::_('index.php?option=com_claw&view='.$view.'&layout=edit&id=' . (int) $this->item->id); ?>"
	method="post" name="adminForm" id="<?php echo $view ?>-form" class="form-validate">

	<div>
		<div class="row">
			<div class="col-md-6">
				<?= $this->form->renderField('event'); ?>
			</div>
			<div class="col-md-6">
				<?= $this->form->renderField('published'); ?>
			</div>
		</div>
		<div class="row">
			<?= $this->form->renderField('value'); ?>
		</div>
	</div>
	
	<?= $this->form->renderField('id'); ?>
	<input type="hidden" name="task" value=""/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
