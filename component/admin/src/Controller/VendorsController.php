<?php
/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2022 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */


namespace ClawCorp\Component\Claw\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
// use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Input\Input;

/**
 * 
 * Vendors list controller class.
 *
 */
class VendorsController extends AdminController
{
	/**
	 * Standard Joomla Constructor
	 *
	 * @param   array                $config   An optional associative array of configuration settings.
	 * Recognized key values include 'name', 'default_task', 'model_path', and
	 * 'view_path' (this list is not meant to be comprehensive).
	 * @param   MVCFactoryInterface  $factory  The factory.
	 * @param   CMSApplication       $app      The Application for the dispatcher
	 * @param   Input                $input    Input
	 *
	 * @since   3.0
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null, $app = null, $input = null)
	{
		parent::__construct($config, $factory, $app, $input);
	}

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  The array of possible config values. Optional.
	 *
	 * @return  \ClawCorp\Component\Claw\Administrator\Model\VendorsModel
	 *
	 * @since   1.6
	 */
	public function getModel($name = 'Vendors', $prefix = 'Administrator', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}

	public function reorder()
  {
    // Check for request forgeries.
    $this->checkToken();

		$filter = $this->app->getInput()->get('filter', '', 'string');
    $event = array_key_exists('event', $filter) ? $filter['event'] : '';

		if ( '' == $event || 'all' == $event )
		{
      $this->app->enqueueMessage('Event selection not valid for deployment.', 'error');
      return false;
		}

		$model = $this->getModel();
		$model->reorder($event);

		$this->app->enqueueMessage('Vendors for '. $event .'have been reordered.', 'info');
		return true;
  }

}
