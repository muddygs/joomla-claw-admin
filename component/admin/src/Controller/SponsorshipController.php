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

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use ClawCorpLib\Traits\Controller;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Input\Input;

/**
 * Controller for a single sponsorship PackageInfo record
 *
 * @since  1.6
 */
class SponsorshipController extends FormController
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

  public function cancel($key = null)
  {
    $result =  parent::cancel($key);
    if ($result) {
      $this->app->setUserState($this->stateContext . '.data', null);
    }
    return $result;
  }

  /**
   * Save implementation that changes use of save2copy to only copy the record
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

    /** @var \ClawCorp\Component\Claw\Administrator\Model\SponsorshipModel $model */
    $model   = $this->model;

    // Determine the name of the primary key for the data.
    if (empty($key)) {
      $key = $this->table->getKeyName();
    }

    $uri = Uri::getInstance();
    $recordId = $uri->getVar('id', 0);

    if (!$this->checkEditId($this->stateContext, $recordId)) {
      // Somehow the person just went to the form and tried to save it. We don't allow that.
      $this->setMessage(Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $recordId), 'error');
      $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list . $this->getRedirectToListAppend(), false));

      return false;
    }

    $this->data[$key] = $recordId;

    // $uri->setVar('id', 0);

    // Access check.
    if (!$this->allowSave($this->data, $key)) {
      $this->setMessage(Text::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'), 'error');
      $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list . $this->getRedirectToListAppend(), false));

      return false;
    }

    // Validate the posted data.
    // Sometimes the form needs some posted data, such as for plugins and modules.
    try {
      $form = $model->getForm($this->data, false);
    } catch (\Exception $e) {
      $this->app->enqueueMessage($e->getMessage(), 'error');
      return false;
    }

    // Test whether the data is valid.
    $validData = $model->validate($form, $this->data);

    // Check for validation errors.
    if ($validData === false) {
      // Get the validation messages.
      $errors = $model->getErrors();

      // Push up to three validation messages out to the user.
      for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
        if ($errors[$i] instanceof \Exception) {
          $this->app->enqueueMessage($errors[$i]->getMessage(), 'warning');
        } else {
          $this->app->enqueueMessage($errors[$i], 'warning');
        }
      }

      // Save the data in the session.
      $this->app->setUserState($this->stateContext . '.data', $this->data);

      // Redirect back to the edit screen.
      $this->setRedirect(
        Route::_('index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($recordId, $key), false)
      );

      return false;
    }

    if ($this->task !== 'save2copy' && !$model->save($validData)) {
      $this->setMessage(Text::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()), 'error');
      $this->app->setUserState($this->stateContext . '.data', $this->data);

      // Redirect back to the edit screen.
      $this->setRedirect(
        Route::_('index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($recordId, $key), false)
      );

      return false;
    }

    if ('save2copy' === $this->task) {
      $data[$key] = 0;
      $data['alias'] = '';
      $data['title'] = $data['title'] . ' (copy)';

      // Model will use this state in loadFormData() to populate the form
      $this->app->setUserState($this->stateContext . '.data', $data);
    }

    switch ($this->task) {
      case 'save2copy':
        $model->setState($this->stateContext . '.id', 0);
        $uri->setVar('id', 0);
        $this->setRedirect($uri->toString());
        return true;
        break;

      case 'apply':
        // Redirect back to the edit screen.
        $this->setRedirect(
          Route::_('index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($recordId, $key), false)
        );
        break;

      default:
        $this->app->setUserState($this->stateContext . '.data', null);
        $uri->setVar('id', null);
        $uri->setVar('layout', null);
        $uri->setVar('view', 'sponsorships');

        // Redirect to the list screen.
        $this->setRedirect(
          Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list . $this->getRedirectToListAppend(), false)
        );
        break;
    }

    return true;
  }
}
