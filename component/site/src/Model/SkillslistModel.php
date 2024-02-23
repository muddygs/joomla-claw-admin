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
use ClawCorpLib\Helpers\Helpers;

use ClawCorpLib\Helpers\Config;
use ClawCorpLib\Helpers\Skills;
use ClawCorpLib\Lib\EventInfo;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Router\Route;
use Joomla\Database\DatabaseDriver;


/**
 * Methods to handle public class listing.
 */
class SkillslistModel extends BaseDatabaseModel
{
  private DatabaseDriver $db;
  private Skills $skills;
  private array $presenters;
  private array $classes;
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
    $this->skills = new Skills($this->db, $eventAlias);
    $this->presenters = $this->skills->GetPresentersList(true);
    $this->classes = $this->skills->GetClassList();

    $config = new Config($eventAlias);

    $simpleList = $listType === 'simple';

    $this->initClassCategories();
    $this->initClassTypes();
    $this->initTabItems();

    $this->populateTabItems($simpleList);


    // Sort tab items day-time-title-id
    foreach ($this->tab_items as $tab => $junk) {
      if ($tab === 'overview') continue;

      ksort($this->tab_items->$tab['ids']);
    }

    $this->setSurveyLink();

    return (object) [
      'items' => $this->classes, // All classes
      'tabs' => $this->tab_items, // Organized by tab
      'categories' => $this->classCategories, // Categories used by overview listing
      'types' => $this->classTypes, // Class types of presentations
      'survey' => $this->surveyLink,
    ];
  }

  private function consolidatePresenters(object $class)
  {
    // Pull in all presenters for this class
    $class->presenter_info = [];

    // Add class owner
    $class->presenter_info[] = [
      'uid' => $class->owner,
      'name' => $this->presenters[$class->owner]->name,
    ];

    // Add co-presenters
    // TODO: Sort by name
    $copresenters = json_decode($class->presenters);

    if ($copresenters !== null) {
      foreach ($copresenters as $copresenter) {
        if (array_key_exists($copresenter, $this->presenters)) {
          $class->presenter_info[] = [
            'uid' => $copresenter,
            'name' => $this->presenters[$copresenter]->name,
          ];
        }
      }
    }
  }

  private function populateTabItems(bool $simpleList) {
    foreach ($this->classes as $class) {
      if ($class->published != 1) continue;

      $titleKey = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $class->title));

      // Collect presenter info for this class
      if ( !$simpleList && $class->day == '0000-00-00' ) continue;

      $this->consolidatePresenters($class);

      // Simple list can be display without days/times
      if ( $simpleList && $class->day == '0000-00-00' ) {
        $tab = 'overview';
        $day = 'z';
        $time = '0000';
        $class->day_text = 'TBA';
      } else {
        // These should all be defined per validation on a published skills class
        $day = Helpers::dateToDayNum($class->day);
        $class->day_text = Helpers::dateToDay($class->day);
        $time = $class->time_slot;

        // Example value: 0900:060 (start time : length)
        $startTime = explode(':', $time)[0];

        switch ($day) {
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
      }
      
      // TODO: SQL has been updated so data is already ordered - this can be simplified
      $ordering = implode('-', [$day, $time, $titleKey, $class->id]);
      $this->tab_items->$tab['ids'][$ordering] = $class->id;

      // Overview view gets all items indexed by category
      $ordering = implode('-', [$titleKey, $class->id]);
      if (array_key_exists($class->category, $this->tab_items->overview['category'])) {
        $this->tab_items->overview['category'][$class->category]['ids'][$ordering] = $class->id;
      }
    }
  }

  private function initClassCategories() {
    $config = new Config($this->eventAlias);
    $this->classCategories = $config->getConfigValuesText(ConfigFieldNames::SKILL_CATEGORY);
  }

  private function initClassTypes() {
    $config = new Config($this->eventAlias);
    $this->classTypes = $config->getConfigValuesText(ConfigFieldNames::SKILL_CLASS_TYPE);
  }

  private function initTabItems() {
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

  private function setSurveyLink() {
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
