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

use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Language\Text;
use ClawCorpLib\Helpers\Helpers;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

/**
 * Get a single presenter submission from an authenticated user
 */
class SkillsubmissionModel extends AdminModel
{
  public function __construct()
  {
    parent::__construct();
    Helpers::sessionSet('skills.submission.tab', 'Classes');
  }

  public function getForm($data = [], $loadData = true)
  {
    // Get the form.
    $form = $this->loadForm(
      'com_claw.skillsubmission',
      'skillsubmission',
      [
        'control' => 'jform',
        'load_data' => $loadData
      ]
    );

    if (empty($form)) {
      return false;
    }

    return $form;
  }

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
    $userState = unserialize(Helpers::sessionGet($token));
    $requestedId = $this->getState($this->getName() . '.id');

    // The View will validated if the record is editable / add allowed
    if ($requestedId && !array_key_exists($requestedId, $userState->skills)) {
      $app->enqueueMessage('You do not have permission to access this resource.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
      $route = Route::_('/');
      $app->redirect($route);
    }

    $data = $this->getItem();

    if ($mergeData) {
      foreach ($mergeData as $key => $value) {
        $data->$key = $value;
      }
    }

    $data->length_info = $data->length_info ?? 60;

    return $data;
  }

  public function getTable($name = '', $prefix = '', $options = array())
  {
    $name = 'Skills';
    $prefix = 'Table';

    if ($table = $this->_createTable($name, $prefix, $options)) {
      return $table;
    }

    throw new \Exception(Text::sprintf('JLIB_APPLICATION_ERROR_TABLE_NAME_NOT_SUPPORTED', $name), 0);
  }
}
