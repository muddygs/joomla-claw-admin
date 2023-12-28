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

$view = 'packageinfo';

?>

<form action="<?= Route::_('index.php?option=com_claw&view=' . $view . '&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="<?= $view ?>-form" class="form-validate">

<h1>Event Information</h1>
<div>
  <div class="row">
    <div class="col-lg-6">
      <?= $this->form->renderField('title'); ?>
      </div>
      <div class="col-lg-6">
        <?= $this->form->renderField('eventId'); ?>
        <?= $this->form->renderField('eventAlias'); ?>
        <?= $this->form->renderField('category'); ?>
      </div>
    </div>
    <div class="row">
      <div class="col-lg-6">
        <?= $this->form->renderField('published'); ?>
      </div>
      <div class="col-lg-6">
        <?= $this->form->renderField('alias'); ?>
      </div>
    </div>
  </div>
  
  <div>
    <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'general')); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', 'Package Info'); ?>

    <div class="row">
      <div class="col-lg-6">
        <?= $this->form->renderField('start'); ?>
      </div>
      <div class="col-lg-6">
        <?= $this->form->renderField('end'); ?>
      </div>
    </div>
    
    <div class="row">
      <div class="col-lg-6">
        <?= $this->form->renderField('packageInfoType'); ?>
        <?= $this->form->renderField('day'); ?>
        <?= $this->form->renderField('start_time'); ?>
        <?= $this->form->renderField('end_time'); ?>
        <?= $this->form->renderField('badgeValue'); ?>
      </div>
      <div class="col-lg-6">
        <?= $this->form->renderField('eventPackageType'); ?>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-6">
        <?= $this->form->renderField('fee'); ?>
      </div>
      <div class="col-lg-6">
        <?= $this->form->renderField('isVolunteer'); ?>
        <?= $this->form->renderField('minShifts'); ?>
      </div>
    </div>

    <?php echo HTMLHelper::_('uitab.endTab'); ?>
    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'discounts', 'Discounts'); ?>
    
    <?= $this->form->renderField('requiresCoupon'); ?>
    <?= $this->form->renderField('couponKey'); ?>
    <?= $this->form->renderField('couponValue'); ?>
    <?= $this->form->renderField('couponOnly'); ?>
    <?= $this->form->renderField('couponAccessGroups'); ?>
    <?= $this->form->renderField('bundleDiscount'); ?>
    
    
    <?php echo HTMLHelper::_('uitab.endTab'); ?>
    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'misc', 'Misc'); ?>

      <?= $this->form->renderField('meta'); ?>
      <?= $this->form->renderField('authNetProfile'); ?>

    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php echo HTMLHelper::_('uitab.endTabSet'); ?>
  </div>

  <input type="hidden" name="task" value="" />
  <?= HTMLHelper::_('form.token'); ?>
</form>