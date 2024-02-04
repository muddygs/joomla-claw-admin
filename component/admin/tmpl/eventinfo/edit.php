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
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

$view = 'eventinfo';

// deal with ordering on new records in the model (put last in parent group)
//			<?= $this->form->renderField('ordering');

?>

<form action="<?= Route::_('index.php?option=com_claw&view=' . $view . '&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="<?= $view ?>-form" class="form-validate">

  <h1>Basic Information</h1>
  <div>
    <div class="row">
      <div class="col-lg-6">
        <?= $this->form->renderField('alias'); ?>
      </div>
      <div class="col-lg-6">
        <?= $this->form->renderField('description'); ?>
      </div>
    </div>
    <div class="row">
      <div class="col-lg-6">
        <?= $this->form->renderField('eventType'); ?>
      </div>
      <div class="col-lg-2">
        &nbsp;
      </div>
      <div class="col-lg-2">
        <?= $this->form->renderField('active'); ?>
      </div>
      <div class="col-lg-2">
        <?= $this->form->renderField('onsiteActive'); ?>
      </div>
    </div>
    <div class="row">
      <div class="col-lg-6">
        <?= $this->form->renderField('prefix'); ?>
      </div>
      <div class="col-lg-6">
        &nbsp;
      </div>
    </div>

    <h1>Dates</h1>

    <div class="row">
      <div class="col-lg-4">
        <?= $this->form->renderField('start_date'); ?>
      </div>
      <div class="col-lg-4">
        <?= $this->form->renderField('end_date'); ?>
      </div>
      <div class="col-lg-4">
        <?= $this->form->renderField('cancelBy'); ?>
      </div>
    </div>

    <h1>Event Booking Configuration</h1>

    <div class="row">
      <div class="col-lg-4">
        <?= $this->form->renderField('ebLocationId'); ?>
      </div>
      <div class="col-lg-4">
        <?= $this->form->renderField('timezone'); ?>
      </div>
      <div class="col-lg-4">
        <?= $this->form->renderField('termsArticleId'); ?>
      </div>
    </div>

    <h1>Registration Mappings</h1>
    <div class="row">
      <?= $this->form->renderField('eb_cat_shifts'); ?>
      <?= $this->form->renderField('eb_cat_supershifts'); ?>
      <?= $this->form->renderField('eb_cat_speeddating'); ?>
      <?= $this->form->renderField('eb_cat_equipment'); ?>
      <?= $this->form->renderField('eb_cat_sponsorship'); ?>
      <?= $this->form->renderField('eb_cat_sponsorships'); ?>
      <?= $this->form->renderField('eb_cat_meals'); ?>
      <?= $this->form->renderField('eb_cat_combomeals'); ?>
      <?= $this->form->renderField('eb_cat_invoicables'); ?>
      
    </div>
  </div>

  <input type="hidden" name="task" value="" />
  <?= HTMLHelper::_('form.token'); ?>
</form>