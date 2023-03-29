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

$view = 'presentersubmission';

$this->document->setTitle("Biography Submission");

$header = $this->params->get('BioHeader') ?? '';
echo $header;

if ($this->params->get('se_submissions_open') == 0) :
?>
  <h1>Submissions are currently closed. You may view only your biography.</h1>
<?php
endif;
?>

<?php if ($this->params->get('se_submissions_open') != 0) : ?>
  <form action="<?php echo Route::_('index.php?option=com_claw&view=' . $view . '&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="<?php echo $view ?>" id="<?php echo $view ?>-form" class="form-validate" enctype="multipart/form-data">
  <?php endif; ?>

  <div class="row form-vertical mb-3">
    <div class="col-12 col-md-6">
      <?php echo $this->form->renderField('name'); ?>
    </div>
    <div class="col-12 col-md-6">
      <?php echo $this->form->renderField('legal_name'); ?>
    </div>
  </div>

  <div class="row">
    <div class="col-12 col-md-6">
      <?php echo $this->form->renderField('photo_upload'); ?>
    </div>
    <div class="col-12 col-md-6">
      <?php echo $this->form->renderField('photo'); ?>
      <?php
        $field = $this->form->getField('photo');
        if ( $field != false && $field->value !== '') {
          if (is_file(implode(DIRECTORY_SEPARATOR, [JPATH_ROOT, $field->value]))) {
            $ts = time();
            ?>
            <p class="form-label"><strong>Preview</strong></p>
            <img src="<?php echo $field->value ?>?ts=<?php echo $ts ?>" />
            <?php
          }
        }
      ?>
      
    </div>
  </div>

  <div class="row">
    <?php echo $this->form->renderField('phone'); ?>
    <?php echo $this->form->renderField('arrival'); ?>
    <?php echo $this->form->renderField('bio'); ?>
    <?php echo $this->form->renderField('social_media'); ?>
  </div>

  <?php if ($this->params->get('se_submissions_open') != 0) : ?>
    <button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('<?php echo $view ?>.submit')">Save</button>
  <?php endif; ?>

  <button type="button" class="btn btn-success" onclick="history.back()">Back</button>

  <input type="hidden" name="idx" value="<?php echo $this->item->id ?>" />
  <input type="hidden" name="task" value="" />
  <?php echo HTMLHelper::_('form.token'); ?>
  <?php if ($this->params->get('se_submissions_open') != 0) : ?>
  </form>
<?php endif; ?>