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

$view = 'equipmentrental';

?>

<form action="<?= Route::_('index.php?option=com_claw&view=' . $view . '&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="<?= $view ?>-form" class="form-validate">

<h1>Event Information</h1>
<div>
  <div class="row">
    <div class="col-lg-6">
      <?= $this->form->renderField('title'); ?>
      <?= $this->form->renderField('description'); ?>
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
  
  <hr />
  
  <div>
    <div class="row">
      <?= $this->form->renderField('fee'); ?>
      <?= $this->form->renderField('meta'); ?>
    </div>

    <div class="row">
      <div class="col-lg-6">
        <?= $this->form->renderField('start'); ?>
      </div>
      <div class="col-lg-6">
        <?= $this->form->renderField('end'); ?>
    </div>

    
  </div>

  <input type="hidden" name="task" value="" />
  <?= HTMLHelper::_('form.token'); ?>
</form>