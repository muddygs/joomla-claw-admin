<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */


namespace ClawCorp\Component\Claw\Site\Controller;

defined('_JEXEC') or die;

use ClawCorpLib\Enums\SkillOwnership;
use ClawCorpLib\Enums\SkillPublishedState;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\Input\Input;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Lib\EventInfo;
use ClawCorpLib\Skills\Presenter;
use ClawCorpLib\Skills\Skill;
use ClawCorpLib\Skills\UserState;
use Joomla\CMS\Router\Route;

/**
 * Controller for a single skill record
 */
class SkillsubmissionController extends FormController
{
  public function __construct(
    $config = [],
    MVCFactoryInterface $factory = null,
    ?CMSApplication $app = null,
    ?Input $input = null,
    FormFactoryInterface $formFactory = null
  ) {
    parent::__construct($config, $factory, $app, $input, $formFactory);

    $task = $input->getCmd('task');

    if ($task == 'submit' && $input != null) {
      $this->save();
    }
  }

  private function checkUserState(): UserState
  {
    $currentToken = Helpers::sessionGet('skill_submission_state', '');

    try {
      $userState = UserState::get($currentToken);
    } catch (\Exception) {
      $this->app->enqueueMessage('Permission denied.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
      $this->setRedirect(Route::_('/'));
    }

    return $userState;
  }

  public function save($key = null, $urlVar = null)
  {
    // Check for request forgeries.
    $this->checkToken();

    $userState = $this->checkUserState();

    /** @var \Joomla\CMS\MVC\Model\FormModel */
    $siteModel = $this->getModel();
    $form = $siteModel->getForm();
    $app = Factory::getApplication();

    $input = $app->input;
    $data = $input->get('jform', [], 'array');
    $data = $form->filter($data);

    $validation = $siteModel->validate($form, $data);

    // Always cache the form data in case an error gets thrown below
    // 'formdata' is merged in the Model::loadFormData() method
    Helpers::sessionSet('formdata', json_encode($data));

    if ($validation === false) {
      $errors = $form->getErrors();

      foreach ($errors as $e) {
        $app->enqueueMessage($e->getMessage(), \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
      }

      return false;
    }

    $data['description'] = trim($data['description']);
    $data['title'] = trim($data['title']);

    // Replace CR/LF with LF for the purposes of our counting
    $tmpDescription = str_replace("\r\n", "\n", $data['description']);
    if (mb_strlen($tmpDescription, 'UTF-8') > 500) {
      $app->enqueueMessage('Description is too long. Please shorten it to 500 characters or less.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
      return false;
    }

    if (mb_strlen($data['title'], 'UTF-8') > 50) {
      $app->enqueueMessage('Title is too long. Please shorten it to 50 characters or less.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
      return false;
    }

    if ($userState->presenter->ownership == SkillOwnership::admin->value) {
      $app->enqueueMessage('Permission Denied: Biography is owned by the admin user.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
      return false;
    }

    // Setup items not included in site model
    $data['event'] = $userState->event;
    $data['ownership'] = SkillOwnership::user->value;
    $data['presenter_id'] = $userState->presenter->id;
    $data['email'] = $userState->presenter->email;
    $data['other_presenter_ids'] = [];

    $data['length_info'] = (int)$data['length_info'] ?? 60;

    // Get id from the session
    //$data['id'] = Helpers::sessionGet('recordid', 0);

    // Force state usage of record id
    unset($data['id']);
    $requestedId = $siteModel->getState($this->getName() . '.id');
    //
    // Inject into the Admin model

    /** @var \ClawCorp\Component\Claw\Administrator\Model\SkillModel */
    $adminModel = $this->getModel('Skill', 'Administrator');
    $adminModel->setState($adminModel->getName() . '.id', $requestedId);

    if (0 == $requestedId) {
      $data['published'] = SkillPublishedState::new->value;
    }

    $result = $adminModel->save($data);

    if ($result) {
      $this->setRedirect(Route::_('index.php?option=com_claw&view=skillssubmissions', 'Class submission save successful.'));
      $app->enqueueMessage('Class description saved.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_INFO);
    } else {
      $app->enqueueMessage('An error occurred during save.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
    }

    return $result;
  }

  public function copyskill()
  {
    $userState = $this->checkUserState();
    $id = $this->input->get('id');
    $skillRoute = Route::_('index.php?option=com_claw&view=skillssubmissions');

    if (!array_key_exists($id, $userState->skills)) {
      $this->app->enqueueMessage('Permission denied.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
      $this->setRedirect(Route::_('/'));
    }

    $presenter = new Presenter($userState->presenter->id);
    if ($presenter->event != $userState->event) {
      $this->setRedirect(Route::_('index.php?option=com_claw&view=skillssubmissions', 'Please submit a biography for the current event before migrating your previous classes.'));
      return;
    }

    $skill = new Skill($id);
    $eventInfo = new EventInfo($userState->event);

    try {
      $newSkill = $skill->migrate($eventInfo, $presenter);
      $this->app->enqueueMessage('Class migrated to current event.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_INFO);
    } catch (\Exception) {
      $this->app->enqueueMessage('Class migration failed.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
      $this->setRedirect($skillRoute);
      return;
    }

    $newSkill->emailResults($eventInfo, $presenter, true);

    $this->setRedirect($skillRoute);

    return;
  }
}
