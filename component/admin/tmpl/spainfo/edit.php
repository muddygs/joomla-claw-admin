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

$view = 'spainfo';
$route = Route::_('index.php?option=com_claw&view=' . $view . '&layout=edit&id=' . (int) $this->item->id);

?>
<form
  action="<?= $route ?>"
  method="post"
  name="adminForm"
  id="<?= $view ?>-form"
  class="form-validate">

  <h1>Spa Session Information</h1>
  <div>
    <div class="row">
      <div class="col-lg-6">
        <?= $this->form->renderField('eventAlias'); ?>
      </div>
      <div class="col-lg-6">
        <?= $this->form->renderField('published'); ?>
      </div>
    </div>
    <div class="row">
      <div class="col-lg-6">
        <?= $this->form->renderField('day'); ?>
        <?= $this->form->renderField('start_time'); ?>
        <?= $this->form->renderField('length'); ?>
      </div>
      <div class="col-lg-6">
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-lg-6">
      <?= $this->form->renderField('fee'); ?>
    </div>
    <div class="col-lg-6">
      <?= $this->form->renderField('alias'); ?>
    </div>
  </div>
  <div class="row">
    <div class="col">
      <?= $this->form->renderField('meta'); ?>
    </div>
  </div>

  <input type="hidden" name="task" value="" />
  <?= HTMLHelper::_('form.token'); ?>
</form>