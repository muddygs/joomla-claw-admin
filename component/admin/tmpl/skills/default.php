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

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Button\PublishedButton;

use ClawCorpLib\Lib\Aliases;

/** @var \Joomla\Component\Banners\Administrator\View\Banners\HtmlView $this */

/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('table.columns');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

$app = Factory::getApplication();
$user = $app->getIdentity();

?>
<div class="container">
<form action="<?php echo Route::_('index.php?option=com_claw&view=skills'); ?>" method="post" name="adminForm" id="adminForm">
  <?php echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>

  <div class="table-responsive">
    <table class="table table-striped table-bordered" id="skillsList">
    <thead>
      <tr>
        <th class="w-1 text-center">
			    <?php echo HTMLHelper::_('grid.checkall'); ?>
		    </th>
        <th scope="col" class="w-1 text-center">
          <?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
        </th>
        <th scole="col">Event</th>
		    <th scope="col">
			    <?php echo HTMLHelper::_('searchtools.sort', 'Title', 'a.title', $listDirn, $listOrder); ?>
		    </th>
		    <th scope="col">Day/Time</th>
		    <th scope="col">Location</th>
        <th scope="col">Track</th>
        <th scope="col">Presenter(s)</th>
        <th scope="col">ID</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ( $this->items AS $i => $item ): ?>
        <tr class="row<?php echo $i % 2; ?>">
          <td class="text-center">
            <?php echo HTMLHelper::_('grid.id', $i, $item->id, false, 'cid', 'cb', $item->title); ?>
          </td>
          <td class="article-status text-center">
            <?php
              $options = [
                'task_prefix' => 'skills.',
                //'disabled' => $workflow_state || !$canChange,
                'id' => 'state-' . $item->id
              ];

              echo (new PublishedButton)->render((int) $item->published, $i, $options);
            ?>
          </td>

          <td>
            <?php echo Aliases::eventTitleMapping[$item->event] ?>
          </td>

          <td>
            <a href="<?php echo Route::_('index.php?option=com_claw&task=skill.edit&id=' . $item->id); ?>"
      			  title="Edit S&amp; Skill Class">
              <?php echo $item->title ?>
            </a>
          </td>
          <td>
            <?php echo $item->day_text ?>
          </td>
          <td>
            <?php echo $item->location_text ?>
          </td>
          <td>
            <?php echo $item->track ?>
          </td>
          <td>
              <?php echo implode('<br/>', $item->presenter_names) ?>
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
