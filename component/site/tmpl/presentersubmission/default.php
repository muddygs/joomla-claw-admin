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

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
	->useScript('form.validate')
  ->useScript('com_claw.textarea')
  ->useStyle('com_claw.textarea');

$view = 'presentersubmission';

$this->document->setTitle("Biography Submission");

$header = $this->params->get('BioHeader') ?? '';
echo $header;

if ( !$this->canEditBio ) {
  if ( $this->item->id != 0) {
    echo "<h1>Submissions are currently closed. You may view only your biography.</h1>";
  } elseif ( $this->canAddOnlyBio) {
    echo "<h1>You may add your bio, but after submission, no further edits are permitted.</h1>";
  } else {
    echo "<h1>Submissions are currently closed.</h1>";    
  }
}
?>

<?php if ($this->canEditBio || ( $this->canAddOnlyBio && 0 == $this->item->id )) : ?>
  <form action="<?php echo Route::_('index.php?option=com_claw&view=' . $view . '&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="<?= $view ?>" id="<?= $view ?>-form" class="form-validate" enctype="multipart/form-data">
<?php endif; ?>

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
      <?php echo $this->form->renderField('copresenter'); ?>
    </div>
    <div class="col-12 col-md-6">
      <?php echo $this->form->renderField('copresenting'); ?>
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
      if ($field != false && $field->value !== '') {
        if (is_file(implode(DIRECTORY_SEPARATOR, [JPATH_ROOT, $field->value]))) {
          $ts = time();
      ?>
          <p class="form-label"><strong>Current Image Preview</strong></p>
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
    <?php echo $this->form->renderField('comments'); ?>
  </div>

  <?php if ($this->canEditBio || ( $this->canAddOnlyBio && 0 == $this->item->id )) : ?>
    <button type="button" class="btn btn-primary" 
      onclick="Joomla.submitbutton('<?php echo $view ?>.submit')">
      Submit for <?php echo $this->eventInfo->description ?>
    </button>
    <?php echo $this->form->renderField('event'); ?>
    <input type="hidden" name="idx" value="<?php echo $this->item->id ?>" />
    <input type="hidden" name="task" value="" />
    <?php echo HTMLHelper::_('form.token'); ?>
  <?php endif; ?>

  <a href="/index.php?option=com_claw&view=skillssubmissions" role="button" class="btn btn-success">Back</a>

<?php if ($this->canEditBio || ( $this->canAddOnlyBio && 0 == $this->item->id )) : ?>
  </form>
<?php endif;
