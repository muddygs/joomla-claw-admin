<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2022 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */


namespace ClawCorp\Component\Claw\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Uri\Uri;
use Joomla\Input\Input;

use ClawCorpLib\Traits\Controller;

/**
 * Controller for a single sponsor record
 *
 * @since  1.6
 */
class PresenterController extends FormController
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

  /**
   * Save implementation that changes use of save2copy to only copy the stored record
   * into a new record and not save the original record.
   * 
   * Based on administrator/components/com_finder/src/Controller/FilterController.php@4.3.4
   * 
   * @param   string  $key     The name of the primary key of the URL variable.
   * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
   *
   * @return  boolean  True if successful, false otherwise.
   */
  public function save($key = null, $urlVar = null)
  {
    // Check for request forgeries.
    $this->checkToken();

    /** @var \ClawCorp\Component\Claw\Administrator\Model\PresenterModel $model */
    $model   = $this->model;

    // Determine the name of the primary key for the data.
    if (empty($key)) {
      $key = $this->table->getKeyName();
    }

    $uri = Uri::getInstance();
    $recordId = $uri->getVar('id', 0);

    // The main thing here is to copy the images and
    // set the old record to indicate the new record's id

    if ($this->task == 'save2copy' && $recordId) {
      $oldRecord = $this->table->load([$key => $recordId]);

      if ($oldRecord !== true) {
        $this->setRedirect(
          'index.php?option=com_claw&view=presenter&layout=edit&id=' . $recordId,
          'Presenter Record Not Found',
          'error'
        );
        return false;
      }
      
      $model->migrateToCurrentEvent($this->table);
      
      $this->table->store();
      $this->setRedirect(
        'index.php?option=com_claw&view=presenter&layout=edit&id=' . $this->table->$key,
        'Presenter Record Copied',
        'message'
      );

      return true;
    }

    return parent::save($key, $urlVar);
  }

}
