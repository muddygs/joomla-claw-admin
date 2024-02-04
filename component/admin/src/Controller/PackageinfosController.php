<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */


namespace ClawCorp\Component\Claw\Administrator\Controller;

defined('_JEXEC') or die;

use ClawCorpLib\Helpers\Deploy;
use ClawCorpLib\Lib\Aliases;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Input\Input;

/**
 * Skills (classes) list controller class.
 *
 * @since  1.6
 */
class PackageinfosController extends AdminController
{
  /**
   * The prefix to use with controller messages.
   *
   * @var    string
   * @since  1.6
   */
  protected $text_prefix = 'COM_CLAW_PACKAGEINFOS';

  public function process()
	{
		$filter = $this->app->getInput()->get('filter', '', 'string');

		$event = array_key_exists('event', $filter) ? $filter['event'] : Aliases::current();

    if ( $event == 'all' ) {
      $app = Factory::getApplication();
      $app->enqueueMessage('Event selection not valid for deployment.', 'error');
      return false;
    }

    $deploy = new Deploy($event, Deploy::PACKAGEINFO);
    $log = $deploy->deploy();
    echo $log;
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
  public function getModel($name = 'Packageinfo', $prefix = 'Administrator', $config = array('ignore_request' => true))
  {
    return parent::getModel($name, $prefix, $config);
  }
}
