<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2022 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted Access');

use ClawCorpLib\Lib\EventConfig;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Button\PublishedButton;

$wa = $this->document->getWebAssetManager();
$wa->useScript('table.columns');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

$app = Factory::getApplication();
$user = $app->getIdentity();

?>
<div class="container">
  <div id="subhead" class="subhead noshadow mb-3">
    <?php echo $this->toolbar->render(); ?>
  </div>
  <form action="<?php echo Route::_('index.php?option=com_claw&view=presenters'); ?>" method="post" name="adminForm" id="adminForm">
    <?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

    <div class="table-responsive">
      <table class="table table-striped table-bordered table-hover" id="presentersList">
        <thead>
          <tr>
            <th class="w-1 text-center">
              <?php echo HTMLHelper::_('grid.checkall'); ?>
            </th>
            <th scope="col" class="w-1 text-center">
              <?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
            </th>
            <th scope="col">
              <?php echo HTMLHelper::_('searchtools.sort', 'Name', 'a.name', $listDirn, $listOrder); ?>
            </th>
            <th scope="col">Event</th>
            <th scope="col">Image</th>
            <th scope="col">Classes</th>
            <th scope="col">
              <?php echo HTMLHelper::_('searchtools.sort', 'Modification', 'a.mtime', $listDirn, $listOrder); ?>  
            </th>
            <th scope="col">
              <?php echo HTMLHelper::_('searchtools.sort', 'Submission', 'a.submission_date', $listDirn, $listOrder); ?>  
            </th>
            <th scope="col">ID</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($this->items as $i => $item) : ?>
            <tr>

              <td class="text-center">
                <?php echo HTMLHelper::_('grid.id', $i, $item->id, false, 'cid', 'cb', $item->name); ?>
              </td>

              <td class="article-status text-center">
                <?php
                $options = [
                  'task_prefix' => 'presenters.',
                  //'disabled' => $workflow_state || !$canChange,
                  'id' => 'published-' . $item->id
                ];

                echo (new PublishedButton)->render((int) $item->published, $i, $options);
                ?>
              </td>

              <td>
                <a href="<?php echo Route::_('index.php?option=com_claw&task=presenter.edit&id=' . $item->id); ?>" title="Edit Presenter (UID=<?= $item->uid?>)">
                  <?php echo $item->name ?>
                </a>
              </td>

              <td>
                <?php echo EventConfig::getTitleMapping()[$item->event] ?? 'TBD' ?>
              </td>

              <td>
                <?php
                  if ( $item->image_preview ?? '' !== '') {
                      ?>
                      <img src="/<?= $item->image_preview ?>" style="max-height:80px; width:auto;" />
                      <?php
                    } else {
                      echo 'No image';
                  }
                ?>
              </td>

              <td>
                <?=$item->classes?>
              </td>

              <td>
                <?php echo $item->mtime ?>
              </td>
              
              <td>
                <?php echo $item->submission_date ?>
              </td>

              <td>
                <?php echo $item->id ?>
              </td>

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