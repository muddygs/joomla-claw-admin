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

use Joomla\CMS\MVC\Controller\AdminController;

/**
 * Eventinfos list controller class.
 *
 * @since  1.6
 */
class EventinfosController extends AdminController
{
  /**
   * The prefix to use with controller messages.
   *
   * @var    string
   * @since  1.6
   */
  protected $text_prefix = 'COM_CLAW_EVENTINFOS';

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
  public function getModel($name = 'Eventinfo', $prefix = 'Administrator', $config = array('ignore_request' => true))
  {
    return parent::getModel($name, $prefix, $config);
  }
}
