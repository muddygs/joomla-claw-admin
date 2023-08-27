<?php
/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\View\Schedule;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
	/**
	 * The \JForm object
	 *
	 * @var  \JForm
	 */
	protected $form;

	/**
	 * The active item
	 *
	 * @var  object
	 */
	protected $item;

	/**
	 * The model state
	 *
	 * @var  object
	 */
	protected $state;

	/**
	 * The actions the user is authorized to perform
	 *
	 * @var  \JObject
	 */
	protected $canDo;

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
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 * @return  void
	 */
	function display($tpl = null)
	{
		$this->form  = $this->get('Form');
		$this->item  = $this->get('Item');
		$this->state = $this->get('State');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		$this->addToolbar();

		parent::display($tpl);
	}

		/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @throws \Exception
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		Factory::getApplication()->input->set('hidemainmenu', true);
		$isNew      = ($this->item->id == 0);

		ToolbarHelper::title(
			'CLAW Schedule ' . ($isNew ? 'Add' : 'Edit'), 'calendar'
		);

		$toolbarButtons = [];

		// If not checked out, can save the item.
		if (true /*!$checkedOut && ($canDo->get('core.edit') || \count($user->getAuthorisedCategories('com_claw', 'core.create')) > 0)*/) {
				ToolbarHelper::apply('schedule.apply');
				$toolbarButtons[] = ['save', 'schedule.save'];

				if (true /*$canDo->get('core.create')*/) {
						$toolbarButtons[] = ['save2new', 'schedule.save2new'];
				}
		}

		// If an existing item, can save to a copy.
		if (!$isNew /*&& $canDo->get('core.create')*/) {
				$toolbarButtons[] = ['save2copy', 'schedule.save2copy'];
		}

		ToolbarHelper::saveGroup(
				$toolbarButtons,
				'btn-success'
		);


		if ($isNew) {
			ToolbarHelper::cancel('schedule.cancel');
		} else {
			ToolbarHelper::cancel('schedule.cancel', 'JTOOLBAR_CLOSE');
		}

		ToolbarHelper::divider();
	}

}
