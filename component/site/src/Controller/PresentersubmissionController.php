<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */


namespace ClawCorp\Component\Claw\Site\Controller;

defined('_JEXEC') or die;

use ClawCorpLib\Enums\SkillOwnership;
use ClawCorpLib\Enums\SkillPublishedState;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\Input\Input;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Lib\EventInfo;
use ClawCorpLib\Skills\Presenter;
use ClawCorpLib\Skills\UserState;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Utility\Utility;

/**
 * Controller for a single sponsor record
 */
class PresentersubmissionController extends FormController
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

    /** @var \Joomla\CMS\MVC\Model\SiteModel */
    $siteModel = $this->getModel();
    $form = $siteModel->getForm();

    $input = $this->app->input;
    $data = $input->get('jform', [], 'array');
    $data = $form->filter($data);
    $validation = $siteModel->validate($form, $data);

    // Always cache the form data in case an error gets thrown below
    // 'formdata' is merged in the Model::loadFormData() method
    Helpers::sessionSet('formdata', json_encode($data));

    if ($validation === false) {
      $errors = $form->getErrors();

      foreach ($errors as $e) {
        $this->app->enqueueMessage($e->getMessage(), \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
      }

      return;
    }

    $files = $input->files->get('jform');
    $existingImage = !is_null($userState->presenter->image_preview);

    if (!$existingImage && (!array_key_exists('photo_upload', $files) || $files['photo_upload']['size'] < 1)) {
      $this->app->enqueueMessage('A representative photo is required', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
      return;
    }

    // From the database - 16MiB
    $dbMaxSize = 16777216;

    $maxSize = min(Utility::getMaxUploadSize(), $dbMaxSize);
    if (array_key_exists('photo_upload', $files) && $files['photo_upload']['size'] > $maxSize) {
      $this->app->enqueueMessage('The photo you uploaded is too large. Please upload a photo less than ' . $maxSize . ' bytes.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
      return;
    }

    $data['bio'] = trim($data['bio']);

    // Replace CR/LF with LF for the purposes of our counting
    $tmpBio = str_replace("\r\n", "\n", $data['bio']);
    if (mb_strlen($tmpBio, 'UTF-8') > 1000) {
      $this->app->enqueueMessage('Biography is too long. Please shorten it to 1000 characters or less.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
      return;
    }

    $identity = $this->app->getIdentity();

    if (is_null($identity) || 0 == $identity->id) {
      throw new \Exception('Permission Denied');
    }

    // Setup items not included in site model
    $data['uid'] = $identity->id;
    $data['email'] = $identity->email;
    $data['ownership'] = SkillOwnership::user->value;

    // presenter_id set in skillssubmissions/HtmlView
    $data['id'] = $userState->presenter->id;
    $data['event'] = $userState->event;

    if ($data['id'] == 0) {
      $data['published'] = SkillPublishedState::new->value;
    } else {
      $data['published'] = $userState->presenter->published;
    }

    /** @var \ClawCorp\Component\Claw\Administrator\Model\PresenterModel */
    $adminModel = $this->getModel('Presenter', 'Administrator');
    $result = $adminModel->save($data);

    // Redirect to the main submission page
    if ($result) {
      $this->app->enqueueMessage('Biography save successful', \Joomla\CMS\Application\CMSApplicationInterface::MSG_INFO);
      $this->setRedirect(Route::_('index.php?option=com_claw&view=skillssubmissions'));
    } else {
      $this->app->enqueueMessage('An error occurred during save.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
      # no redirect, stay on page $this->setRedirect(Route::_('index.php?option=com_claw&view=skillssubmissions'));
    }

    return;
  }

  public function copybio()
  {
    $userState = $this->checkUserState();

    $id = $userState->presenter->id;

    if (0 == $id) {
      $this->app->enqueueMessage('Copy failed.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
      $this->setRedirect(Route::_('index.php?option=com_claw&view=skillssubmissions'));
      return;
    }

    $eventInfo = new EventInfo($userState->event);
    $presenter = Presenter::get($id);

    try {
      $newPresenter = $presenter->migrate($eventInfo);
      $this->app->enqueueMessage('Biography migrated to current event.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_INFO);
    } catch (\Exception) {
      $this->app->enqueueMessage('Biography migration failed.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
      $this->setRedirect(Route::_('index.php?option=com_claw&view=skillssubmission'));
      return;
    }

    $newPresenter->emailResults(true);

    // Update state and redirect to the editor
    $userState->setPresenter($newPresenter);
    Helpers::sessionSet($userState->getToken(), serialize($userState));

    $this->setRedirect(Route::_('index.php?option=com_claw&view=presentersubmission'));

    return;
  }
}
