<?php
/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2022 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\View\Shifts;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Lib\Registrants;

/**
 * @package     Joomla.Administrator
 * @subpackage  com_claw
 *
 * @copyright   Copyright (C) 2020 John Smith. All rights reserved.
 * @license     GNU General Public License version 3; see LICENSE
 */

/**
 * Main "Hello World" Admin View
 */
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
	 * @var    Registry
	 * @since  1.6
	 */
	protected $state;

	/**
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 * @return  void
	 */
	function display($tpl = null)
	{
		/** @var SkillsModel $model */
		$model               = $this->getModel();
		$this->state         = $model->getState();
		$this->items         = $model->getItems();
		$this->pagination    = $model->getPagination();
		$this->filterForm    = $model->getFilterForm();
		$this->activeFilters = $model->getActiveFilters();

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
		$toolbar = Toolbar::getInstance('toolbar');

		$user  = $app->getIdentity();

		if ($user->authorise('core.admin', 'com_claw')) {
			$toolbar->addNew('shift.add');

			$toolbar->delete('shift.delete')
			->text('Delete')
			->listCheck(true);
		}

		$toolbar->basicButton('process','Create Events','shifts.process')
    ->icon('fas fa-calendar')
    ->buttonClass('btn')
		->listCheck(false);

		ToolbarHelper::divider();

		//$childBar = $dropdown->getChildToolbar();
		//$childBar->save2copy('events.duplicate', 'Duplicate')->listCheck(true);

		// if ($user->authorise('core.edit.state', 'com_countrybase'))
		// {
		// 	$dropdown = $toolbar->dropdownButton('status-group')
		// 	->text('JTOOLBAR_CHANGE_STATUS')
		// 	->toggleSplit(false)
		// 	->icon('icon-ellipsis-h')
		// 	->buttonClass('btn btn-action')
		// 	->listCheck(true);

		// 	$childBar = $dropdown->getChildToolbar();

		// 	$childBar->publish('countries.publish')->listCheck(true);

		// 	$childBar->unpublish('countries.unpublish')->listCheck(true);

		// 	$childBar->archive('countries.archive')->listCheck(true);

		// 	if ($this->state->get('filter.published') != -2)
		// 	{
		// 		$childBar->trash('countries.trash')->listCheck(true);
		// 	}
		// }

		// if ($this->state->get('filter.published') == -2 && $user->authorise('core.delete', 'com_countrybase'))
		// {
		// 	$toolbar->delete('countries.delete')
		// 	->text('JTOOLBAR_EMPTY_TRASH')
		// 	->message('JGLOBAL_CONFIRM_DELETE')
		// 	->listCheck(true);
		// }

		// if ($user->authorise('core.admin', 'com_countrybase') || $user->authorise('core.options', 'com_countrybase'))
		// {
		// 	$toolbar->preferences('com_countrybase');
		// }

		// $tmpl = $app->input->getCmd('tmpl');
		// if ($tmpl !== 'component')
		// {
		// 	ToolbarHelper::help('countrybase', true);
		// }
	}
}
