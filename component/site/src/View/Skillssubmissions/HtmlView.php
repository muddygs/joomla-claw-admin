<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
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

/** @package ClawCorp\Component\Claw\Site\Controller */
class HtmlView extends BaseHtmlView
{
  public bool $canEdit = false;
  public bool $canAddOnly = false;
  public ?EventInfo $currentEventInfo = null;

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

    /** @var \Joomla\CMS\Application\SiteApplication */
    $app = Factory::getApplication();
    $this->params = $params = $app->getParams();
    $group = $params->get('se_group', '');
    $groups = $app->getIdentity()->getAuthorisedGroups();

    if ($group == 0 || !in_array($group, $groups)) {
      $app->enqueueMessage('You do not have permission to access this resource. Please sign in.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);

      // Redirect to login
      $return = \Joomla\CMS\Uri\Uri::getInstance()->toString();
      $url    = 'index.php?option=com_users&view=login';
      $url   .= '&return='.base64_encode($return);
      $app->redirect($url);  
    }

    $bioCandidate = $this->findNewestBio();
    $this->bio = $bioCandidate ?? (object)[];
    $this->classes = $this->findUnarchivedClasses();
    
    // Check for errors.
    $errors = $this->get('Errors');
    if ($errors != null && count($errors)) {
      throw new GenericDataException(implode("\n", $errors), 500);
    }

    $this->canEditBio = $this->params->get('se_submissions_open') != 0;
    $this->canAddOnlyBio = $this->params->get('se_submissions_bioonly') != 0;

    parent::display($tpl);
  }

  private function findNewestBio(): ?object
  {
    /** @var \ClawCorp\Component\Claw\Site\Model\SkillssubmissionsModel */
    $model = $this->getModel();

    $aliases = EventInfo::getEventInfos();
    /** @var \ClawCorpLib\Lib\EventInfo */
    foreach ( $aliases AS $alias => $eventInfo ) {
      // Skip newer events
      if ( $eventInfo->end_date > $this->currentEventInfo->end_date ) continue;
      if ( $eventInfo->eventType != EventTypes::main ) continue;

      $bio = $model->GetPresenterBio($eventInfo);
      if ( !is_null($bio)) {
        return $bio;
        break;
      }
    }

    return null;
  }

  /**
   * @return array Array of class objects 
   */
  private function findUnarchivedClasses(): array
  {
    $classes = [];

    /** @var \ClawCorp\Component\Claw\Site\Model\SkillssubmissionsModel */
    $model = $this->getModel();

    $aliases = EventInfo::getEventInfos();
    /** @var \ClawCorpLib\Lib\EventInfo */
    foreach ( $aliases AS $alias => $eventInfo ) {
      // Skip newer events
      if ( $eventInfo->end_date > $this->currentEventInfo->end_date ) continue;
      if ( $eventInfo->eventType != EventTypes::main ) continue;

      $eventClasses = $model->GetPresenterClasses($eventInfo);
      if ( !is_null($classes)) {
        $classes = array_merge($classes, $eventClasses);
      }
    }

    return $classes;
  }
}
