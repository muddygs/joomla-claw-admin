<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Site\Model;

defined('_JEXEC') or die;

use ClawCorpLib\Enums\ConfigFieldNames;
use ClawCorpLib\Enums\SkillPublishedState;

use ClawCorpLib\Helpers\Config;
use ClawCorpLib\Iterators\PresenterArray;
use ClawCorpLib\Iterators\SkillArray;
use ClawCorpLib\Lib\EventInfo;
use ClawCorpLib\Skills\Presenters;
use ClawCorpLib\Skills\Skills;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Router\Route;


/**
 * Methods to handle public class listing.
 */
class SkillslistModel extends BaseDatabaseModel
{
  private SkillArray $skills;
  private PresenterArray $presenters;
  private string $eventAlias;

  private $tab_items;
  private $classCategories;
  private $classTypes;
  private $surveyLink;

  public function __construct()
  {
    parent::__construct();

    $this->db = $this->getDatabase();
    $this->tab_items = (object)[];
  }

  public function GetConsolidatedList(string $eventAlias, string $listType): object
  {
    $this->eventAlias = $eventAlias;
    $eventInfo = new EventInfo($eventAlias);
    $this->presenters = Presenters::get($eventInfo, true);
    $this->skills = Skills::get($eventInfo, SkillPublishedState::published);

    $simpleList = $listType === 'simple';

    $this->initClassCategories();
    $this->initClassTypes();
    $this->initTabItems();

    $this->populateTabItems($simpleList);

    $this->setSurveyLink();

    // $this->list
    return (object) [
      'skillArray' => $this->skills,
      'presenterArray' => $this->presenters,
      'tabs' => $this->tab_items, // Organized by tab
      'categories' => $this->classCategories, // Categories used by overview listing
      'types' => $this->classTypes, // Class types of presentations
      'survey' => $this->surveyLink,
    ];
  }

  private function populateTabItems(bool $simpleList)
  {
    /** @var \ClawCorpLib\Skills\Skill */
    foreach ($this->skills as $skill) {
      if ($simpleList || (!$simpleList && is_null($skill->day))) {
        if (array_key_exists($skill->category, $this->tab_items->overview['category'])) {
          $this->tab_items->overview['category'][$skill->category]['ids'][] = $skill->id;
        }
        continue;
      }

      $dayNum = $skill->day->format('w');
      if ($dayNum < 2) $dayNum += 7;

      $time = $skill->time_slot;

      // Example value: 0900:060 (start time : length)
      $startTime = intval(explode(':', $time, 2)[0]);

      switch ($dayNum) {
        case 5:
          $tab = 'fri';
          $tab .= ($startTime < 1200) ? 'am' : 'pm';
          break;
        case 6:
          $tab = 'sat';
          $tab .= ($startTime < 1200) ? 'am' : 'pm';
          break;
        case 7:
          $tab = 'sun';
          break;
      }

      $this->tab_items->$tab['ids'][] = $skill->id;

      // Overview view gets all items indexed by category
      if (array_key_exists($skill->category, $this->tab_items->overview['category'])) {
        $this->tab_items->overview['category'][$skill->category]['ids'][] = $skill->id;
      }
    }
  }

  private function initClassCategories()
  {
    $config = new Config($this->eventAlias);
    $this->classCategories = $config->getConfigValuesText(ConfigFieldNames::SKILL_CATEGORY);
  }

  private function initClassTypes()
  {
    $config = new Config($this->eventAlias);
    $this->classTypes = $config->getConfigValuesText(ConfigFieldNames::SKILL_CLASS_TYPE);
  }

  private function initTabItems()
  {
    // Prepare data for views
    $this->tab_items = (object)[];

    // Prepare data references for detailed views
    foreach (['Overview', 'Fri AM', 'Fri PM', 'Sat AM', 'Sat PM', 'Sun'] as $time) {
      // Remove spaces and lowercase
      $name = strtolower(str_replace(' ', '', $time));
      $this->tab_items->$name = [];

      $this->tab_items->$name = [
        'name' => $time,
        'ids' => [],
      ];
    }

    $this->tab_items->overview['category'] = [];

    foreach ($this->classCategories as $key => $category) {
      $this->tab_items->overview['category'][$key] = [
        'name' => $category,
        'ids' => [],
      ];
    }
  }

  private function setSurveyLink()
  {
    // Is the event onsite active true?
    $eventInfo = new EventInfo($this->eventAlias);

    $this->surveyLink = '';

    if ($eventInfo->onsiteActive) {
      /** @var \Joomla\CMS\Application\SiteApplication */
      $app = Factory::getApplication();
      $params = $app->getParams();
      $seSurveyMenuId = $params->get('se_survey_link', 0);

      if ($seSurveyMenuId > 0) {
        $menu = $app->getMenu();
        $item = $menu->getItem($seSurveyMenuId);
        $this->surveyLink = Route::_($item->link);
      }
    }
  }
}
