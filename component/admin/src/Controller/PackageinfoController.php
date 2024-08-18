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

/**
 * Controller for a single event info record
 *
 * @since  1.6
 */
class PackageinfoController extends FormController
{
  // TODO: WTH?
  protected function createModel($name, $prefix = '', $config = [])
  {
    if (!isset($config['context']))
      $config['context'] = $this->controllerContext();

    return parent::createModel($name, $prefix, $config);
  }

  public function cancel($key = null)
  {
    $result =  parent::cancel($key);
    if ($result) {
      $context = $this->controllerContext();
      $this->app->setUserState($context . '.data', null);
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

    /** @var \ClawCorp\Component\Claw\Administrator\Model\PackageinfoModel $model */
    $model   = $this->getModel();
    $table   = $model->getTable();
    $data    = $this->input->post->get('jform', [], 'array');
    $context = $this->controllerContext();
    $task    = $this->getTask();

    // Determine the name of the primary key for the data.
    if (empty($key)) {
      $key = $table->getKeyName();
    }

    $uri = Uri::getInstance();
    $recordId = $uri->getVar('id', 0);

    if (!$this->checkEditId($context, $recordId)) {
      // Somehow the person just went to the form and tried to save it. We don't allow that.
      $this->setMessage(Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $recordId), 'error');
      $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list . $this->getRedirectToListAppend(), false));

      return false;
    }

    $data[$key] = $recordId;

    // $uri->setVar('id', 0);

    // Access check.
    if (!$this->allowSave($data, $key)) {
      $this->setMessage(Text::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'), 'error');
      $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list . $this->getRedirectToListAppend(), false));

      return false;
    }

    // Validate the posted data.
    // Sometimes the form needs some posted data, such as for plugins and modules.
    $form = $model->getForm($data, false);

    if (!$form) {
      $this->app->enqueueMessage($model->getError(), 'error');
      return false;
    }

    // Test whether the data is valid.
    $validData = $model->validate($form, $data);

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
      $this->app->setUserState($context . '.data', $data);

      // Redirect back to the edit screen.
      $this->setRedirect(
        Route::_('index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($recordId, $key), false)
      );

      return false;
    }

    if ($task !== 'save2copy' && !$model->save($validData)) {
      $this->setMessage(Text::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()), 'error');
      $this->app->setUserState($context . '.data', $data);

      // Redirect back to the edit screen.
      $this->setRedirect(
        Route::_('index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($recordId, $key), false)
      );

      return false;
    }

    if ('save2copy' === $task) {
      $data[$key] = 0;
      $data['alias'] = '';
      $data['eventPackageType'] = 0;
      $data['couponKey'] = '';
      $data['eventId'] = 0;
      $data['title'] = 'New Record';

      // Model will use this state in loadFormData() to populate the form
      $this->app->setUserState($context . '.data', $data);
    }

    switch ($task) {
      case 'save2copy':
        $model->setState($this->context . '.id', 0);
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
        $this->app->setUserState($context . '.data', null);
        $uri->setVar('id', null);
        $uri->setVar('layout', null);
        $uri->setVar('view', 'packageinfos');

        // Redirect to the list screen.
        $this->setRedirect(
          Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list . $this->getRedirectToListAppend(), false)
        );
        break;
    }

    return true;
  }

  private function controllerContext(): string
  {
    return implode('.', [$this->option, 'edit', $this->context]);
  }
}
