<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

$view = 'Skillsubmission';

$this->document->setTitle("Skill Submission");

echo $this->params->get('SkillHeader') ?? '';

$canSubmit = $this->params->get('se_submissions_open');

if ($canSubmit == 0) :
?>
  <h1>Submissions are currently closed. You may view only your class description.</h1>
<?php
else :
?>
  <h1 class="text-center w-100 border border-info p-3">You are submitting for <?php echo $this->eventInfo->description ?>.</h1>
<?php
endif;
?>

<?php if ( $canSubmit != 0 ) : ?>
  <form action="<?php echo Route::_('index.php?option=com_claw&view=' . $view . '&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="<?php echo $view ?>" id="<?php echo $view ?>-form" class="form-validate" enctype="multipart/form-data">
<?php endif; ?>

  <div class="row form-vertical mb-3">
    <div class="col-9">
      <?php echo $this->form->renderField('title'); ?>
    </div>
    <div class="col-3">
      <?php echo $this->form->renderField('length'); ?>
    </div>
  </div>

  <div class="row">
    <?php echo $this->form->renderField('equipment_info'); ?>
    <?php echo $this->form->renderField('copresenter_info'); ?>
    <?php echo $this->form->renderField('requirements_info'); ?>
    <?php echo $this->form->renderField('description'); ?>
    <?php echo $this->form->renderField('comments'); ?>
  </div>

  <hr />

  <div class="row">
    <h3>
  </div>

  <?php if ($canSubmit != 0) : ?>
    <button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('<?php echo $view ?>.submit')">Submit for <?php echo $this->eventInfo->description ?></button>
  <?php endif; ?>

  <a href="/index.php?option=com_claw&view=skillssubmissions" role="button" class="btn btn-success">Back</a>

  <input type="hidden" name="idx" value="<?php echo $this->item->id ?>" />
  <input type="hidden" name="task" value="" />
  <?php echo HTMLHelper::_('form.token'); ?>
  <?php if ($this->params->get('se_submissions_open') != 0) : ?>
  </form>
<?php endif;