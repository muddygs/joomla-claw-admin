<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */


namespace ClawCorp\Component\Claw\Administrator\Controller;

defined('_JEXEC') or die;

use ClawCorpLib\Traits\Controller;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Router\Route;
use Joomla\Input\Input;

/**
 * Eventinfos list controller class.
 *
 * @since  1.6
 */
class EventinfosController extends AdminController
{
  use Controller;

  public function __construct(
    $config = [],
    MVCFactoryInterface $factory = null,
    ?CMSApplication $app = null,
    ?Input $input = null,
    FormFactoryInterface $formFactory = null
  ) {
    parent::__construct($config, $factory, $app, $input, $formFactory);

    $this->controllerSetup();
  }

  public function save2copy()
  {
    // Check for request forgeries.
    $this->checkToken();

    if (count($this->cid) != 1) {
      $this->setMessage('1 (and only 1) row must be selected.', 'error');
      $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
      return false;
    }

    $result = $this->table->load($this->cid[0]);

    if (!$result) {
      $this->setMessage('Error loading old row.', 'error');
      $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
      return false;
    }

    // TODO: check for potential alias conflicts
    $this->table->alias = $this->table->alias . ' (copy)';
    $this->table->id = 0;

    // Redirect back to the list screen.
    $this->setRedirect(
      Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false)
    );

    $this->table->store();

    return true;
  }

  public function delete()
  {
    // Check for request forgeries.
    $this->checkToken();

    if (count($this->cid) != 1) {
      $this->setMessage('1 (and only 1) row must be selected.', 'error');
      $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
      return false;
    }

    $result = $this->table->load($this->cid[0]);
    if ($result) $this->table->delete();

    // Redirect back to the list screen.
    $this->setRedirect(
      Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false)
    );

    return $result;
  }

  public function publish()
  {
    // Check for request forgeries.
    $this->checkToken();

    if (count($this->cid) != 1) {
      $this->setMessage('1 (and only 1) row must be selected.', 'error');
      $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
      return false;
    }

    $result = $this->table->load($this->cid[0]);
    if ($result) {
      $this->table->active = $this->task == 'unpublish' ? 0 : 1;
      $this->table->store();
    }

    // Redirect back to the list screen.
    $this->setRedirect(
      Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false)
    );

    return $result;
  }
}
