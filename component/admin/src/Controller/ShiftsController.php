<?php
/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */


namespace ClawCorp\Component\Claw\Administrator\Controller;

\defined('_JEXEC') or die;

use ClawCorpLib\Grid\Grids;
use ClawCorpLib\Lib\Aliases;
use Joomla\CMS\MVC\Controller\AdminController;

/**
 * Shifts list controller class.
 */
class ShiftsController extends AdminController
{
	public function process()
	{
		$filter = $this->app->getInput()->get('filter', '', 'string');

		$event = array_key_exists('event', $filter) ? $filter['event'] : Aliases::current();

    switch ($event) {
      case '':
      case '_current_':
        $event = Aliases::current();
        break;
      case '_all_':
        $event = '';
    }

		$grid = new Grids($event);
		$grid->createEvents();
	}

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  The array of possible config values. Optional.
	 *
	 * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel
	 *
	 * @since   1.6
	 */
	public function getModel($name = 'Shift', $prefix = 'Administrator', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}

}
