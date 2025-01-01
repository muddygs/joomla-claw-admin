<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Site\View\Skillssubmissions;

defined('_JEXEC') or die;

use ClawCorpLib\Enums\EventTypes;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Lib\EventInfo;
use ClawCorpLib\Lib\EventInfos;
use ClawCorp\Component\Claw\Site\Model\SkillssubmissionsModel;
use ClawCorpLib\Enums\SkillOwnership;
use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Skills\Presenter;
use ClawCorpLib\Skills\Skills;
use ClawCorpLib\Skills\UserState;

/** @package ClawCorp\Component\Claw\Site\Controller */
class HtmlView extends BaseHtmlView
{
  public bool $canSubmit = false;
  public bool $canAddBioOnly = false;
  public bool $bioIsCurrent = false;

  public EventInfo $currentEventInfo;
  public SkillssubmissionsModel $model;

  public function __construct($config = array())
  {
    parent::__construct($config);
    $this->currentEventInfo = new EventInfo(Aliases::current(true));
  }

  /**
   * Execute and display a template script.
   *
   * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
   *
   * @return  void
   */
  public function display($tpl = null)
  {
    $this->state = $this->get('State');
    $this->model = $this->getModel();

    /** @var \Joomla\CMS\Application\SiteApplication */
    $app = Factory::getApplication();
    $this->params = $app->getParams();
    $uid = $app->getIdentity()->id;

    $currentToken = Helpers::sessionGet('skill_submission_state', '');
    $this->userState = new UserState($uid, $currentToken);

    $this->bio = $this->findNewestBio($uid);

    if (!is_null($this->bio)) {
      $this->bio->loadImageBlobs();
      $this->userState->setPresenter($this->bio);
    }

    $this->classes = Skills::getByUid($uid);

    /** @var \ClawCorpLib\Skills\Skill */
    foreach ($this->classes as $class) {
      $this->userState->addSkill($class);
    }

    // bio and classes
    $this->userState->submissionsOpen = $this->params->get('se_submissions_open', 0) != 0;
    // bio only
    $this->userState->submissionsBioOnly = $this->params->get('se_submissions_bioonly', 0) != 0;


    // So while the userState is newly created, we now use the permissions Check
    if (!$this->userState->isValid()) {
      $app->enqueueMessage('You do not have permission to access this resource. Please sign in.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);

      // Redirect to login
      $return = \Joomla\CMS\Uri\Uri::getInstance()->toString();
      $url    = 'index.php?option=com_users&view=login';
      $url   .= '&return=' . base64_encode($return);
      $app->redirect($url);
    }

    Helpers::sessionSet('skill_submission_state', $this->userState->getToken());
    Helpers::sessionSet($this->userState->getToken(), serialize($this->userState));

    // Check for errors.
    $errors = $this->get('Errors');
    if ($errors != null && count($errors)) {
      throw new GenericDataException(implode("\n", $errors), 500);
    }

    parent::display($tpl);
  }

  private function findNewestBio(int $uid): ?Presenter
  {
    // ordered by date DESC, so first hit is newest
    $eventInfos = new EventInfos(withUnpublished: true);

    /** @var \ClawCorpLib\Lib\EventInfo */
    foreach ($eventInfos as $eventInfo) {
      // Skip newer events
      if ($eventInfo->end_date > $this->currentEventInfo->end_date) continue;
      if ($eventInfo->eventType != EventTypes::main) continue;

      $presenter = Presenter::getByUid($eventInfo, $uid, SkillOwnership::user);

      if (!is_null($presenter)) {
        return $presenter;
        break;
      }
    }

    return null;
  }
}
