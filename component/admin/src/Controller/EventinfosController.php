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
use Joomla\CMS\Router\Route;

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

  public function save2copy()
  {
    // Check for request forgeries.
    $this->checkToken();

    /** @var \ClawCorp\Component\Claw\Administrator\Model\EventInfosModel $model */
    $model   = $this->getModel();
    $table   = $model->getTable();
    $cid    = $this->input->post->get('cid', [], 'array');

    if ( count($cid) != 1 ) {
      $this->setMessage('1 (and only 1) row must be selected.', 'error');
      $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
      return false;
    }

    $result = $table->load($cid[0]);

    if ( !$result ) {
      $this->setMessage('Error loading old row.', 'error');
      $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
      return false;
    }

    $table->alias = $table->alias . ' (copy)';
    $table->id = 0;

    // Redirect back to the list screen.
    $this->setRedirect(
      Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false)
    );

    $table->store();
    
    return true;
  }

  public function delete()
  {
    // Check for request forgeries.
    $this->checkToken();

    /** @var \ClawCorp\Component\Claw\Administrator\Model\EventInfosModel $model */
    $model   = $this->getModel();
    $table   = $model->getTable();
    $cid    = $this->input->post->get('cid', [], 'array');

    if ( count($cid) != 1 ) {
      $this->setMessage('1 (and only 1) row must be selected.', 'error');
      $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
      return false;
    }

    $result = $table->load($cid[0]);
    if ( $result ) $table->delete();

    // Redirect back to the list screen.
    $this->setRedirect(
      Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false)
    );
    
    return $result;
  }
}
