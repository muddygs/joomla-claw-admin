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

use ClawCorpLib\Lib\Ebmgmt;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\Input\Json;

/**
 * Shifts list controller class.
 */
class EventcopyController extends AdminController
{
  public function repair()
  {
    Ebmgmt::rebuildEventIdMapping();
    echo 'Rebuild complete. Press back to continue.';
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
  public function getModel($name = 'Eventcopy', $prefix = 'Administrator', $config = array('ignore_request' => true))
  {
    return parent::getModel($name, $prefix, $config);
  }

  public function doCopyEvent()
  {
    $this->checkToken();

    $json = new Json();
    /** @var \ClawCorp\Component\Claw\Administrator\Model\EventcopyModel */
    $model = $this->getModel('Eventcopy');
    $text = $model->doCopyEvent($json);
    header('Content-Type: text/plain');
    echo $text;
  }
}
