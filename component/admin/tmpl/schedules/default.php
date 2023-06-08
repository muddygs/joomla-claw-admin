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

use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;

use ClawCorpLib\Helpers\Sponsors;
use ClawCorpLib\Lib\Aliases;
use Joomla\CMS\Button\PublishedButton;

$wa = $this->document->getWebAssetManager();
$wa->useScript('table.columns');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

?>
<div class="container">
<form action="<?=Route::_('index.php?option=com_claw&view=schedules'); ?>" method="post" name="adminForm" id="adminForm">
  <?=LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

  <div class="table-responsive">
    <table class="table table-striped table-bordered" id="schedulesList">
    <thead>
      <tr>
        <th class="w-1 text-center">
			    <?=HTMLHelper::_('grid.checkall'); ?>
		    </th>
        <th scope="col" class="w-1 text-center">
          <?=HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
        </th>
        <th scope="col">
			    <?=HTMLHelper::_('searchtools.sort', 'Event', 'a.event', $listDirn, $listOrder); ?>
		    </th>
        <th scope="col">
			    <?=HTMLHelper::_('searchtools.sort', 'Day', 'a.day', $listDirn, $listOrder); ?>
		    </th>
		    <th scope="col">
			    <?=HTMLHelper::_('searchtools.sort', 'Start Time', 'a.start_time', $listDirn, $listOrder); ?>
		    </th>
        <th scope="col">End Time</th>
		    <th scope="col">Title</th>
        <th scope="col">Location</th>
        <th scole="col">Sponsor(s)</th>
        <th scope="col">ID</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ( $this->items AS $i => $item ): ?>
        <tr class="row<?php echo $i % 2; ?>">
          <td class="text-center">
            <?=HTMLHelper::_('grid.id', $i, $item->id, false, 'cid', 'cb', $item->event_title); ?>
          </td>
          <td class="article-status text-center">
                <?php
                $options = [
                  'task_prefix' => 'schedules.',
                  //'disabled' => $workflow_state || !$canChange,
                  'id' => 'published-' . $item->id
                ];

                echo (new PublishedButton)->render((int) $item->published, $i, $options);
                ?>
              </td>
          <td>
            <?=Aliases::eventTitleMapping[$item->event] ?? 'TBD' ?>
          </td>
          <td>
            <?= $item->day_text ?? '' ?>
          </td>
          <td>
            <?=$item->start_time_text ?>
          </td>
          <td>
            <?=$item->end_time_text ?>
          </td>
          <td>
            <a href="<?php echo Route::_('index.php?option=com_claw&task=schedule.edit&id=' . $item->id); ?>"
      			  title="Edit Event">
              <?=$item->event_title ?>
            </a>
          </td>
          <td>
            <?=$item->location_text ?>
          </td>
          <td>
            <?php
              $sponsors = json_decode($item->sponsors);
              $names = [];
              foreach ( $sponsors AS $s ):
                $names[] = ($this->sponsors)->GetSponsorById($s)->name;
              endforeach;
              echo implode('<br/>', $names);
            ?>
          </td>
          <td>
            <?=$item->id ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
    </table>
  </div>

  <?=$this->pagination->getListFooter(); ?>
  <input type="hidden" name="task" value="">
  <input type="hidden" name="boxchecked" value="0">
  <?=HTMLHelper::_('form.token'); ?>

</form>
</div>
