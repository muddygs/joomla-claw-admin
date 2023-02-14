<?php
/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2022 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;

use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Helpers\Skills;

/**
 * Methods to handle a list of records.
 *
 * @since  1.6
 */
class SkillsModel extends ListModel
{

	private array $list_fields = [
		'id',
		'published',
		'title',
		'event',
		'day',
		'start_time',
		'length',
		'location',
		'presenters'
	];	

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = [];
			
			foreach( $this->list_fields AS $f )
			{
				$config['filter_fields'][] = 'a.'.$f;
			}
		}

		parent::__construct($config);

		$this->SetFilterForm();
	}

	private function SetFilterForm()
	{
		$f = $this->getFilterForm();

		/** @var $filter \Joomla\CMS\Form\FormField */
		$filter = $f->getGroup('filter')['filter_day'];
		foreach(['Fri','Sat','Sun'] AS $day) {
			$filter->addOption($day, ['value' => $day]);
		}

		/** @var $filter \Joomla\CMS\Form\FormField */
		$filter = $f->getGroup('filter')['filter_event'];
		foreach(Aliases::eventTitleMapping AS $alias => $title ) {
			$filter->addOption($title, ['value' => $alias]);
		}

		/** @var $filter \Joomla\CMS\Form\FormField */
		$filter = $f->getGroup('filter')['filter_presenter'];
		foreach(Skills::GetPresentersList($this->getDatabase()) AS $p ) {
			$filter->addOption($p->name, ['value' => $p->id]);
		}
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   3.0.1
	 */
	protected function populateState($ordering = 'title', $direction = 'ASC')
	{
		$app = Factory::getApplication();

		// List state information
		$value = $app->input->get('limit', $app->get('list_limit', 0), 'uint');
		$this->setState('list.limit', $value);

		$value = $app->input->get('limitstart', 0, 'uint');
		$this->setState('list.start', $value);

		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);


		// List state information.
		parent::populateState($ordering, $direction);
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since   1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . serialize($this->getState('filter.title'));
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.state');
		//$id .= ':' . serialize($this->getState('filter.tag'));

		return parent::getStoreId($id);
	}

	/**
	 * Get the master query for retrieving a list of countries subject to the model state.
	 *
	 * @return  \Joomla\Database\DatabaseQuery
	 *
	 * @since   1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select', array_map( function($a) use($db) { return $db->quoteName('a.'.$a); }, $this->list_fields)
			)
		)
			->from($db->quoteName('#__claw_skills', 'a'));

		// Filter by search in title.
		$search = $this->getState('filter.search');
		$day = $this->getState('filter.day');
		$event = $this->getState('filter.event') ?? Aliases::current;

		if (!empty($search))
		{
			$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
			$query->where( 'a.title LIKE ' . $search );
		}

		$query->where('a.event LIKE ' . $db->quote($event));

		if ( $day )
			$query->where('a.day LIKE ' . $db->quote($day));

		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering', 'a.title');
		$orderDirn = $this->state->get('list.direction', 'ASC');

		$query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));
		return $query;
	}
}
