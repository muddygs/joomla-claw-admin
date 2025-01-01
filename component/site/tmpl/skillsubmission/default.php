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

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
  ->useScript('form.validate')
  ->useScript('com_claw.textarea')
  ->useStyle('com_claw.textarea');

$view = 'Skillsubmission';

$this->document->setTitle("Skill Submission");
?>
<h1>Skills Class Submission</h1>
<?php

$canSubmit = $this->canSubmit;
/** @var \ClawCorpLib\Skills\UserState */
$userState = $this->userState;

if (!$canSubmit) :
?>
  <h1>Submissions are currently closed. You may view only your class description.</h1>
<?php
else :
?>
  <h1 class="text-center w-100 border border-info p-3">You are submitting for <?= $this->eventInfo->description ?>.</h1>
<?php
endif;
?>

<?php if ($canSubmit): ?>
  <form action="<?= Route::_('index.php?option=com_claw&view=' . $view . '&layout=edit&id=' . (int) $this->item->id) ?>" method="post" name="<?= $view ?>" id="<?= $view ?>-form" class="form-validate" enctype="multipart/form-data">
  <?php endif; ?>

  <div class="row form-vertical mb-3">
    <div class="col-lg-6">
      <?= $this->form->renderField('title') ?>
    </div>
    <div class="col-lg-3">
      <?= $this->form->renderField('length_info') ?>
    </div>
    <div class="col-lg-3">
      <?= $this->form->renderField('av') ?>
    </div>
  </div>

  <div class="row">
    <?= $this->form->renderField('equipment_info') ?>
    <?= $this->form->renderField('copresenter_info') ?>
    <?= $this->form->renderField('requirements_info') ?>
    <?= $this->form->renderField('description') ?>
    <?= $this->form->renderField('comments') ?>
  </div>

  <hr />

  <div class="row">
    <h3>
  </div>

  <?php if ($canSubmit): ?>
    <button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('<?= $view ?>.submit')">Submit for <?= $this->eventInfo->description ?></button>
  <?php endif; ?>

  <a href="/index.php?option=com_claw&view=skillssubmissions" role="button" class="btn btn-success">Back</a>

  <input type="hidden" name="idx" value="<?= $this->item->id ?>" />
  <input type="hidden" name="task" value="" />
  <?= HTMLHelper::_('form.token') ?>
  <?php if ($canSubmit) : ?>
  </form>
<?php endif;
