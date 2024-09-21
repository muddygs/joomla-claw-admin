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
use Joomla\CMS\Button\PublishedButton;
use Joomla\CMS\User\UserFactoryInterface;

$wa = $this->document->getWebAssetManager();
$wa->useScript('table.columns');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

$app = Factory::getApplication();
$userFactory = Factory::getContainer()->get(UserFactoryInterface::class);

$view = "spainfos";

?>
<div class="container">
  <div id="subhead" class="subhead noshadow mb-3">
    <?php echo $this->toolbar->render(); ?>
  </div>
  <form action="<?php echo Route::_('index.php?option=com_claw&view=' . $view); ?>" method="post" name="adminForm" id="adminForm">
    <?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

    <div class="table-responsive">
      <table class="table table-striped table-bordered table-hover" id="<?= $view ?>List">
        <thead>
          <tr>
            <th class="w-1 text-center">
              <?php echo HTMLHelper::_('grid.checkall'); ?>
            </th>
            <th scope="col" class="w-1 text-center">
              <?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
            </th>
            <th scope="col">
              EB Links
            </th>
            <th scope="col">
              <?php echo HTMLHelper::_('searchtools.sort', 'Title', 'a.title', $listDirn, $listOrder); ?>
            </th>
            <th scope="col">Start</th>
            <th scope="col">End</th>
            <th scope="col">ID</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($this->items as $i => $item) : ?>
            <?php
            $meta = json_decode($item->meta);
            ?>
            <tr class="row<?php echo $i % 2; ?>">
              <td class="text-center">
                <?php echo HTMLHelper::_('grid.id', $i, $item->id, false, 'cid', 'cb', $item->alias); ?>
              </td>

              <td class="article-status text-center">
                <?php
                $options = [
                  'task_prefix' => 'spainfos.',
                  //'disabled' => $workflow_state || !$canChange,
                  'id' => 'published-' . $item->id
                ];

                echo (new PublishedButton)->render((int) $item->published, $i, $options);
                ?>
              </td>

              <td>
                <?php if (!is_null($meta)):
                  foreach ($meta as $key => $value):
                    $user = $userFactory->loadUserById($value->userid);

                    if ($value->eventId > 0): ?>
                      <div><?= $user->name ?><a href="<?php echo Route::_('index.php?option=com_eventbooking&view=event&id=' . $value->eventId); ?>" title="Edit in Event Booking" target="_blank">
                          <?= $value->eventId ?></div>
                      </a>
                    <?php else: ?>
                      <div><?= $user->name ?></div>
                    <?php
                    endif ?>
                <?php endforeach;
                endif ?>

              </td>

              <td>
                <a href="<?php echo Route::_('index.php?option=com_claw&task=spainfo.edit&id=' . $item->id); ?>" title="Edit Package Info">
                  <?php echo $item->title ?>
                </a>
              </td>

              <td>
                <?php
                $start = new DateTime($item->start);
                echo $start->format('D g:i A');
                ?>
              </td>

              <td>
                <?php
                $end = new DateTime($item->end);
                echo $end->format('D g:i A');
                ?>
              </td>

              <td>
                <?= $item->id ?>
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
