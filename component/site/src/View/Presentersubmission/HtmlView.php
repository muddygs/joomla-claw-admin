<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Site\View\Presentersubmission;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Lib\EventInfo;
use ClawCorpLib\Skills\UserState;
use Joomla\CMS\Router\Route;

/** @package ClawCorp\Component\Claw\Site\Controller */
class HtmlView extends BaseHtmlView
{
  public bool $canEdit = false;
  public bool $canAddOnly = false;
  public ?EventInfo $eventInfo = null;

  public function __construct($config = array())
  {
    parent::__construct($config);

    $this->eventInfo = new EventInfo(Aliases::current(true));
  }

  public function display($tpl = null)
  {
    $this->state = $this->get('State');
    $this->form  = $this->get('Form');
    /** @var \Joomla\CMS\Object\CMSObject */
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
      $app->enqueueMessage('You do not have permission to access this resource.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
      $route = Route::_('/');
      $app->redirect($route);
    }

    $canSubmit = $userState->submissionsOpen || $userState->submissionsBioOnly;

    if (!$canSubmit) {
      $app->enqueueMessage('Submissions are closed.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
      $route = Route::_('/');
      $app->redirect($route);
    }

    if (!is_null($userState->presenter) && !$userState->isBioCurrent()) {
      $app->enqueueMessage('Editing of old biographies is not permitted. Please resubmit to the current event.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
      $route = Route::_('/index.php?option=com_claw&view=skillssubmissions');
      $app->redirect($route);
    }

    // Validation of ownership of the record performed in the model

    // Check for errors.
    $errors = $this->get('Errors');
    if ($errors != null && count($errors)) {
      throw new GenericDataException(implode("\n", $errors), 500);
    }

    // In read-only mode? New bios accepted, but current ones are locked
    if (!$canSubmit && !is_null($userState->presenter)) {
      $fieldSet = $this->form->getFieldset('userinput');
      foreach ($fieldSet as $field) {
        $this->form->setFieldAttribute($field->getAttribute('name'), 'readonly', 'true');
      }
    }

    # used in controller for validating image upload requirement during save task
    Helpers::sessionSet('has_image', false);

    if (!is_null($userState->presenter) && !is_null($userState->presenter->image_preview)) {
      Helpers::sessionSet('has_image', true);
    }

    $this->userState = $userState;
    parent::display($tpl);
  }

  /**
   * Return the component-specific model (PSR helper)
   * @return \ClawCorp\Component\Claw\Site\Model\PresentersubmissionModel
   */
  public function getModel($name = null)
  {
    return parent::getModel($name);
  }
}
