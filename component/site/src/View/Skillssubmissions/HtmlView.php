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

use ClawCorpLib\Enums\ConfigFieldNames;
use ClawCorpLib\Enums\EventTypes;
use ClawCorpLib\Helpers\Config;
use ClawCorpLib\Helpers\DbBlob;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Lib\EventInfo;
use ClawCorpLib\Lib\EventInfos;
use ClawCorp\Component\Claw\Site\Model\SkillssubmissionsModel;

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
    $this->params = $params = $app->getParams();
    $group = $params->get('se_group', '');
    $groups = $app->getIdentity()->getAuthorisedGroups();

    if ($group == 0 || !in_array($group, $groups)) {
      $app->enqueueMessage('You do not have permission to access this resource. Please sign in.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);

      // Redirect to login
      $return = \Joomla\CMS\Uri\Uri::getInstance()->toString();
      $url    = 'index.php?option=com_users&view=login';
      $url   .= '&return=' . base64_encode($return);
      $app->redirect($url);
    }

    $bioCandidate = $this->findNewestBio();
    $this->bio = (object)[];

    if (!is_null($bioCandidate)) {
      $this->bio = $bioCandidate;

      // append image_preview to the presenter object
      $config = new Config($this->currentEventInfo->alias);
      $path = $config->getConfigText(ConfigFieldNames::CONFIG_IMAGES, 'presenters') ?? '/images/skills/presenters/cache';

      $itemIds = [$this->bio->id];
      $itemMinAges = [new \DateTime($this->bio->mtime, new \DateTimeZone('UTC'))];

      // Insert property for cached presenter preview image
      $cache = new DbBlob(
        db: $this->model->getDatabase(),
        cacheDir: JPATH_ROOT . $path,
        prefix: 'web_',
        extension: 'jpg'
      );

      $filenames = $cache->toFile(
        tableName: '#__claw_presenters',
        rowIds: $itemIds,
        key: 'image_preview',
        minAges: $itemMinAges
      );

      $this->bio->image_preview = $filenames[$this->bio->id] ?? '';
      $this->bioIsCurrent = $this->bio->event == $this->currentEventInfo->alias;
    }

    $this->classes = $this->findUnarchivedClasses();

    // Check for errors.
    $errors = $this->get('Errors');
    if ($errors != null && count($errors)) {
      throw new GenericDataException(implode("\n", $errors), 500);
    }

    $this->canSubmit = $this->params->get('se_submissions_open', 0) != 0;
    $this->canAddBioOnly = $this->params->get('se_submissions_bioonly', 0) != 0;

    parent::display($tpl);
  }

  private function findNewestBio(): ?object
  {
    $eventInfos = new EventInfos(withUnpublished: true);

    /** @var \ClawCorpLib\Lib\EventInfo */
    foreach ($eventInfos as $eventInfo) {
      // Skip newer events
      if ($eventInfo->end_date > $this->currentEventInfo->end_date) continue;
      if ($eventInfo->eventType != EventTypes::main) continue;

      $bio = $this->model->GetPresenterBio($eventInfo);
      if (!is_null($bio)) {
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

    $eventInfos = new EventInfos(withUnpublished: true);
    /** @var \ClawCorpLib\Lib\EventInfo */
    foreach ($eventInfos as $eventInfo) {
      // Skip newer events
      if (
        $eventInfo->end_date > $this->currentEventInfo->end_date
        || $eventInfo->eventType != EventTypes::main
      ) {
        continue;
      }

      $eventClasses = $this->model->GetPresenterClasses($eventInfo);
      if (!is_null($classes)) {
        $classes = array_merge($classes, $eventClasses);
      }
    }

    return $classes;
  }
}
