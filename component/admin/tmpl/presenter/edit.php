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

$view = 'presenter';

?>

<form action="<?php echo Route::_('index.php?option=com_claw&view='.$view.'&layout=edit&id=' . (int) $this->item->id); ?>"
	method="post" name="adminForm" id="<?php echo $view ?>-form" class="form-validate" enctype="multipart/form-data">

	<div class="row form-vertical mb-3">
    <div class="col-12 col-md-6">
        <?php echo $this->form->renderField('name'); ?>
    </div>
    <div class="col-12 col-md-6">
        <?php echo $this->form->renderField('legal_name'); ?>
    </div>
	</div>
	<div class="row form-vertical mb-3">
		<div class="col-12 col-md-6">
        <?php echo $this->form->renderField('event'); ?>
    </div>
    <div class="col-12 col-md-6">
        <?php echo $this->form->renderField('published'); ?>
    </div>
	</div>

	<div>
		<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'general')); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', 'General'); ?>

		<div class="row">
			<?php echo $this->form->renderField('uid'); ?>
			<?php echo $this->form->renderField('phone'); ?>
			<?php echo $this->form->renderField('bio'); ?>
		</div>
		
		<div class="row">
			<div class="col-6">
				<?php echo $this->form->renderField('photo_upload'); ?>
			</div>
			<div class="col-6">
				<?php echo $this->form->renderField('photo'); ?>
				<?php
					if ( $this->item->photo !== '') {
						if (is_file(implode(DIRECTORY_SEPARATOR, [JPATH_ROOT, $this->item->photo]))) {
							$ts = time();
							?>
							<img src="<?php echo $this->item->photo ?>?ts=<?php echo $ts ?>" />
							<?php
						}
					}
				?>
				
			</div>
		</div>

		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'special', 'Special'); ?>
		<div class="row">
			<?php echo $this->form->renderField('social_media'); ?>
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
