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

$view = 'configuration';
?>
<h1>Danger Zone</h1>
<p class="text-danger">Incorrect configuration can cause serious registration problems. Please be careful when editing these settings.</p>
<form action="<?= Route::_('index.php?option=com_claw&view=' . $view . '&layout=edit&id=' . (int) $this->item->id); ?>" 
      method="post" 
      name="adminForm"
      id="<?= $view ?>-form" 
      class="form-validate">

  <div>
    <div class="row">
      <div class="col">
        <?= $this->form->renderField('event'); ?>
      </div>
    </div>
    <div class="row">
      <div class="col">
        <?= $this->form->renderField('fieldname'); ?>
      </div>
    </div>
    <div class="row">
      <div class="col">
        <?= $this->form->renderField('value'); ?>
      </div>
    </div>
    <div class="row">
      <div class="col">
        <?= $this->form->renderField('text'); ?>
      </div>
    </div>
  </div>

  <input type="hidden" name="task" value="" />
  <?= HTMLHelper::_('form.token'); ?>
</form>