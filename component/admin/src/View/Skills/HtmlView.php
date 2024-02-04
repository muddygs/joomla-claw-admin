<?php
/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\View\Skills;

defined('_JEXEC') or die;

use ClawCorpLib\Enums\ConfigFieldNames;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

use ClawCorpLib\Helpers\Config;
use ClawCorpLib\Lib\Aliases;

/**
 * View class for CLAW Skills & Education listing
 *
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
		/** @var \ClawCorp\Component\Claw\Administrator\Model\SkillsModel */
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

		$event = array_key_exists('event', $this->activeFilters) ? $this->activeFilters['event'] : Aliases::current();
		$config = new Config($event);
		$this->skill_class_types = $config->getConfigValuesText(ConfigFieldNames::SKILL_CLASS_TYPE);

		parent::display($tpl);
	}

	protected function addToolbar(): void
	{
		$app = Factory::getApplication();

		ToolbarHelper::title('CLAW Skills Classes');

		// Get the toolbar object instance
		$toolbar = Toolbar::getInstance('toolbar');

		$user  = $app->getIdentity();

		if ($user->authorise('core.admin', 'com_claw') || $user->authorise('claw.skills', 'com_claw')) {
			$toolbar->addNew('skill.add');

			$toolbar->delete('skills.delete')
			->text('Delete')
			->message('Confirm delete selected class(es)?')
			->listCheck(true);
		}
	}
}
