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

$view = 'vendor';

?>

<form action="<?php echo Route::_('index.php?option=com_claw&view='.$view.'&layout=edit&id=' . (int) $this->item->id); ?>"
	method="post" name="adminForm" id="<?php echo $view ?>-form" class="form-validate">

	<div class="row form-vertical mb-3">
    <div class="col-12 col-md-8">
        <?php echo $this->form->renderField('name'); ?>
    </div>
    <div class="col-12 col-md-4">
        <?php echo $this->form->renderField('published'); ?>
    </div>
	</div>

	<div class="row form-vertical mb-3">
    <div class="col-6">
        <?php echo $this->form->renderField('event'); ?>
    </div>
    <div class="col-6">
        <?php echo $this->form->renderField('spaces'); ?>
    </div>
	</div>

	<div class="row">
		<?php echo $this->form->renderField('description'); ?>
		<?php echo $this->form->renderField('link'); ?>
		<?php echo $this->form->renderField('logo'); ?>
		<?php echo $this->form->renderField('location'); ?>
	</div>
	
	<input type="hidden" name="id" value="<?=$this->item->id?>"/>
	<input type="hidden" name="task" value=""/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
