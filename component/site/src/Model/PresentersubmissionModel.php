<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Language\Text;
use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Skills\UserState;
use Joomla\CMS\Router\Route;

/**
 * Get a single presenter submission from an authenticated user
 */
class PresentersubmissionModel extends AdminModel
{
  public function __construct()
  {
    parent::__construct();
    Helpers::sessionSet('skills.submission.tab', 'Biography');
  }

  public function getForm($data = [], $loadData = true)
  {
    // Get the form.
    $form = $this->loadForm('com_claw.presentersubmission', 'presentersubmission', array('control' => 'jform', 'load_data' => $loadData));

    if (empty($form)) {
      return false;
    }

    return $form;
  }

  /**
   * Get the data for the form; assumes Skillssubmission/HtmlView has already set the id of the data id
   * @return array 
   */
  protected function loadFormData()
  {
    /** @var \Joomla\CMS\Application\SiteApplication */
    $app = Factory::getApplication();
    $mergeData = null;
    $submittedFormData = Helpers::sessionGet('formdata');
    if ($submittedFormData) {
      $mergeData = json_decode($submittedFormData, true);
    }

    $token = Helpers::sessionGet('skill_submission_state', '');

    if (!$token) {
      $app->enqueueMessage('You do not have permission to access this resource.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
      $route = Route::_('/');
      $app->redirect($route);
    }

    /** @var \ClawCorpLib\Skills\UserState */
    $userState = UserState::get($token);

    if (!$userState->isValid()) {
      $app->enqueueMessage('You do not have permission to access this resource.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
      $route = Route::_('/');
      $app->redirect($route);
    }

    $this->setState($this->getName() . '.id', $userState->presenter->id ?? 0);

    $data = $this->getItem();
    $data->arrival = json_decode($data->arrival);

    if ($mergeData) {
      $data = (object) array_merge((array) $data, $mergeData);
    }

    return $data;
  }

  public function getTable($name = '', $prefix = '', $options = array())
  {
    $name = 'Presenters';
    $prefix = 'Table';

    if ($table = $this->_createTable($name, $prefix, $options)) {
      return $table;
    }

    throw new \Exception(Text::sprintf('JLIB_APPLICATION_ERROR_TABLE_NAME_NOT_SUPPORTED', $name), 0);
  }
}
