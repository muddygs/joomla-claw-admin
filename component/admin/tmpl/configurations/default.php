<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;

$wa = $this->document->getWebAssetManager();
$wa->useScript('table.columns');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

$app = Factory::getApplication();
$user = $app->getIdentity();

$view = "configurations";

?>
<div class="container">
  <div id="subhead" class="subhead noshadow mb-3">
    <?php echo $this->toolbar->render(); ?>
  </div>
  <form action="<?php echo Route::_('index.php?option=com_claw&view='.$view); ?>" method="post" name="adminForm" id="adminForm">
    <?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

    <h1>Danger Zone</h1>
    <p class="text-danger">Incorrect configuration can cause serious registration problems. Please be careful when editing these settings.</p>

    <div class="table-responsive">
      <table class="table table-striped table-bordered table-hover" id="<?= $view ?>List">
        <thead>
          <tr>
            <th scope="col">Section</th>
            <th scope="col">Key</th>
            <th scope="col">Value</th>
            <th scope="col">ID</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($this->items as $i => $item) : ?>
            <tr>
              <td><?= $item->fieldname ?></td>
              <td><?= $item->value ?></td>
              <td>
                <a href="<?php echo Route::_('index.php?option=com_claw&task=configuration.edit&id=' . $item->id); ?>"
      			      title="Edit <?php echo $this->escape($item->text); ?>">
                  <?=$item->text?>
                </a>
              </td>

              <td><?= $item->id ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="row">
      <?php echo $this->pagination->getListFooter(); ?>
    </div>

    <input type="hidden" name="task" value="">
    <input type="hidden" name="boxchecked" value="0">
    <?php echo HTMLHelper::_('form.token'); ?>

  </form>
</div>