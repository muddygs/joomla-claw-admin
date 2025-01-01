<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Site\View\Skillsubmission;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Lib\EventInfo;
use ClawCorpLib\Skills\UserState;
use Joomla\CMS\Router\Route;

/** @package ClawCorp\Component\Claw\View\Skillsubmission */
class HtmlView extends BaseHtmlView
{
  public function display($tpl = null)
  {
    $this->state = $this->get('State');
    $this->form  = $this->get('Form');
    $this->item  = $this->get('Item');

    if ($this->item === false) {
      throw new GenericDataException('Item not found', 404);
    }

    /** @var \Joomla\CMS\Application\SiteApplication */
    $app = Factory::getApplication();

    $currentToken = Helpers::sessionGet('skill_submission_state', '');
    $permission = false;

    try {
      $userState = UserState::get($currentToken);
      $permission = true;
    } catch (\Exception) {
      $permission = false;
    }

    if (!$permission || !$userState->isValid()) {
      throw new GenericDataException('You do not have permission to edit this record.', 403);
    }

    $canSubmit = $userState->submissionsOpen;

    if (!$canSubmit) {
      $app->enqueueMessage('Submissions are closed.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
      $route = Route::_('/index.php?option=com_claw&view=skillssubmissions');
      $app->redirect($route);
    }

    if (!$userState->isBioCurrent()) {
      $app->enqueueMessage('Editing of classes is not permitted. Please resubmit your biography to the current event.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
      $route = Route::_('/index.php?option=com_claw&view=skillssubmissions');
      $app->redirect($route);
    }

    $requestedId = $this->item->id ?? 0;

    if ($requestedId) {
      if (!array_key_exists($requestedId, $userState->skills)) {
        throw new GenericDataException('You do not have permission to edit this record.', 403);
      }

      if ($userState->skills[$requestedId]->event != $userState->event) {
        $app->enqueueMessage('Please resubmit this class to the current event.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
        $route = Route::_('/index.php?option=com_claw&view=skillssubmissions');
        $app->redirect($route);
      }
    }

    // Check for errors.
    $errors = $this->get('Errors');
    if ($errors != null && count($errors)) {
      throw new GenericDataException(implode("\n", $errors), 500);
    }

    // Event Naming
    $this->eventInfo = new EventInfo($userState->event);
    $this->userState = $userState;
    $this->canSubmit = $canSubmit;

    parent::display($tpl);
  }
}
