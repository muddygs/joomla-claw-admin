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

// Set default (should one not exist, model will assign
$ordering = $this->item->ordering ?? -1;

$view = 'location';

// deal with ordering on new records in the model (put last in parent group)
//			<?php echo $this->form->renderField('ordering');

?>

<form action="<?php echo Route::_('index.php?option=com_claw&view='.$view.'&layout=edit&id=' . (int) $this->item->id); ?>"
	method="post" name="adminForm" id="<?php echo $view ?>-form" class="form-validate">

	<div>
		<div class="row">
			<?php echo $this->form->renderField('catid'); ?>
			<?php echo $this->form->renderField('value'); ?>
			<?php echo $this->form->renderField('alias'); ?>
			<?php echo $this->form->renderField('id'); ?>
		</div>
	</div>
	
	<input type="hidden" name="ordering" value="<?php echo $ordering ?>"/>
	<input type="hidden" name="task" value=""/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
