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
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

$view = 'shift';

?>

<form action="<?php echo Route::_('index.php?option=com_claw&view=' . $view . '&layout=edit&id=' . (int) $this->item->id); ?>"
  method="post" name="adminForm" id="<?php echo $view ?>-form" class="form-validate">

  <?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>

  <div>
    <div class="row">
      <?= $this->form->renderField('description') ?>
      <?= $this->form->renderField('event') ?>
      <?= $this->form->renderField('category') ?>
      <?= $this->form->renderField('requirements') ?>
      <?= $this->form->renderField('coordinators') ?>
      <?= $this->form->renderField('grid') ?>
      <?= $this->form->renderField('id') ?>
    </div>
  </div>

  <input type="hidden" name="task" value="">
  <?= HTMLHelper::_('form.token') ?>
</form>
