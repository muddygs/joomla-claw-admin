<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\View\Shifts;

defined('_JEXEC') or die;

use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Lib\ClawEvents;
use ClawCorpLib\Lib\EventInfo;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
  /**
   * The search tools form
   *
   * @var    Form
   * @since  1.6
   */
  public $filterForm;

  /**
   * The active search filters
   *
   * @var    array
   * @since  1.6
   */
  public $activeFilters = [];

  /**
   * Category data
   *
   * @var    array
   * @since  1.6
   */
  protected $categories = [];

  /**
   * An array of items
   *
   * @var    array
   * @since  1.6
   */
  protected $items = [];

  /**
   * The pagination object
   *
   * @var    Pagination
   * @since  1.6
   */
  protected $pagination;

  /**
   * The model state
   *
   * @var    \Joomla\CMS\Object\CMSObject
   * @since  1.6
   */
  protected $state;

  /**
   * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
   * @return  void
   */
  function display($tpl = null)
  {
    /** @var \ClawCorp\Component\Claw\Administrator\Model\ShiftsModel $model */
    $model               = $this->getModel();
    $this->state         = $model->getState();
    $this->items         = $model->getItems();
    $this->pagination    = $model->getPagination();
    $this->filterForm    = $model->getFilterForm();
    $this->activeFilters = $model->getActiveFilters();

    $eventAlias = $this->activeFilters['event'] ?? Aliases::current(true);

    try {
      $eventInfo = new EventInfo($eventAlias);
    } catch (\Exception) {
      Factory::getApplication()->enqueueMessage('Invalid event alias.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
      return false;
    }


    /** @var \Joomla\CMS\Form\Field\ListField */
    $parentField = $this->filterForm->getField('shift_area', 'filter');

    // TODO: replace shift_area column with category_id column (also Grids.php, ShiftsModel.php, shift view)
    $shiftCategoryIds = [...$eventInfo->eb_cat_shifts, ...$eventInfo->eb_cat_supershifts];
    $shiftRawCategories = ClawEvents::getRawCategories($shiftCategoryIds);

    foreach ($shiftRawCategories as $alias => $row) {
      // remove 'shifts-' prefix
      $k = substr($alias, 7);
      $parentField->addOption(htmlentities($row->name), ['value' => $k]);
    }

    // $state = $model->getState('filter.shift_area');
    // $this->filterForm->setFieldAttribute('shift_area', 'query', $state, 'filter');

    // Flag indicates to not add limitstart=0 to URL
    $this->pagination->hideEmptyLimitstart = true;

    // Check for errors.
    if (count($errors = $this->get('Errors'))) {
      throw new GenericDataException(implode("\n", $errors), 500);
    }

    $this->addToolbar();

    parent::display($tpl);
  }

  protected function addToolbar(): void
  {
    $app = Factory::getApplication();

    ToolbarHelper::title('Shifts');

    // Get the toolbar object instance
    $toolbar = Toolbar::getInstance();

    $user  = $app->getIdentity();

    if ($user->authorise('core.admin', 'com_claw')) {
      $toolbar->addNew('shift.add');

      $toolbar->delete('shifts.delete')
        ->text('Deleted Selected')
        ->message('Confirm delete selected shift(s)?')
        ->listCheck(true);

      $toolbar->basicButton('process', 'Deploy Events', 'shifts.process')
        ->icon('fas fa-calendar')
        ->buttonClass('btn')
        ->listCheck(false);

      $toolbar->basicButton('repair', 'Repair Events', 'shifts.repair')
        ->icon('fas fa-tools')
        ->buttonClass('btn')
        ->listCheck(false);

      // TODO: This is not implemented, but I might want to in the future
      // $toolbar->confirmButton('reset','Reset Events','shifts.reset')
      // ->icon('fas fa-exclamation-triangle')
      // ->buttonClass('btn')
      // ->listCheck(false)
      // ->message('Are you sure you want to reset all events?');

    }

    ToolbarHelper::divider();
  }
}
