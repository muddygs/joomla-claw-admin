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
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\AdminController;

/**
 * Equipmentrentals list controller class.
 *
 * @since  1.6
 */
class EquipmentrentalsController extends AdminController
{
  /**
   * The prefix to use with controller messages.
   *
   * @var    string
   * @since  1.6
   */
  protected $text_prefix = 'COM_CLAW_EQUIPMENTRENTALS';

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
        $app = Factory::getApplication();
        $app->enqueueMessage('Event selection not valid for deployment.', 'error');
        return false;
        break;
    }

    $deploy = new Deploy($event, Deploy::EQUIPMENTRENTAL);
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
   * @return  \Joomla\CMS\MVC\Model\AdminModel  The model.
   *
   * @since   1.6
   */
  public function getModel($name = 'Equipmentrental', $prefix = 'Administrator', $config = array('ignore_request' => true))
  {
    return parent::getModel($name, $prefix, $config);
  }
}
