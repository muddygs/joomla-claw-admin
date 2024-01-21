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
use ClawCorpLib\Lib\ClawEvents;
use ClawCorpLib\Lib\EventInfo;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Router\Route;

/**
 * Methods to handle public class listing.
 */
class SkillslistModel extends BaseDatabaseModel
{
  public function GetConsolidatedList(string $eventAlias): object
  {
    $db = $this->getDatabase();
    $skills = new Skills($db, $eventAlias);
    $presenters = $skills->GetPresentersList(true);
    $classes = $skills->GetClassList();

    $classTypes = Config::getColumn(ConfigFieldNames::SKILL_CLASS_TYPE);
    $classCategories = Config::getColumn(ConfigFieldNames::SKILL_CATEGORY);

    // Prepare data for views

    $tab_items = (object)[
      'overview' => [],
      'friam' => [],
      'fripm' => [],
      'satam' => [],
      'satpm' => [],
      'sun' => [],
    ];

    // Prepare data references for detailed views
    foreach ( ['Overview', 'Fri AM', 'Fri PM', 'Sat AM', 'Sat PM', 'Sun'] AS $time ) {
      // Remove spaces and lowercase
      $name = strtolower(str_replace(' ', '', $time));

      $tab_items->$name = [
        'name' => $time,
        'ids' => [],
      ];
    }

    $tab_items->overview['category'] = [];

    foreach ( $classCategories AS $category ) {
      $tab_items->overview['category'][$category->value] = [
        'name' => $category->text,
        'ids' => [],
      ];
    }

    foreach ( $classes AS $class ) {
      if ( $class->published != 1 ) continue;

      if ( $class->day == '0000-00-00' ) {
        // var_dump($class);
        continue;
      }

      // These should all be defined per validation on a published skills class
      $day = Helpers::dateToDayNum($class->day);
      $class->day_text = Helpers::dateToDay($class->day);
      $time = $class->time_slot;
      $title = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $class->title));

      // TODO: SQL has been updated so data is already ordered - this can be simplified
      $ordering = implode('-', [$day, $time, $title, $class->id]);

      // Overview view gets all items indexed by category
      if ( array_key_exists($class->category, $tab_items->overview['category']) ) {
        $tab_items->overview['category'][$class->category]['ids'][$ordering] = $class->id;
      }

      // Detailed view gets items indexed by day, time

      // Example value: 0900:060 (start time : length)
      $startTime = explode(':', $time)[0];

      switch ( $day ) {
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

      $tab_items->$tab['ids'][$ordering] = $class->id;

      $class->presenter_info = [];

      // Add class owner
      $class->presenter_info[] = [
        'uid' => $class->owner,
        'name' => $presenters[$class->owner]->name,
      ];

      // Add co-presenters
      // TODO: Sort by name
      $copresenters = json_decode($class->presenters);

      if ( $copresenters !== null ) {
        foreach ( $copresenters AS $copresenter ) {
          if ( array_key_exists($copresenter, $presenters) ) {
            $class->presenter_info[] = [
              'uid' => $copresenter,
              'name' => $presenters[$copresenter]->name,
            ];
          }
        }
      }
    }

    // Sort tab items by key
    foreach ( $tab_items AS $tab => $items ) {
      if ( $tab === 'overview' ) continue;

      ksort($tab_items->$tab['ids']);
    }

    // Is the event onsite active true?
    $eventInfo = new EventInfo($eventAlias);

    $surveyLink = '';

    if ( $eventInfo->onsiteActive) {
      /** @var $app SiteApplication */
      $app = Factory::getApplication();
      $params = $app->getParams();
      $seSurveyMenuId = $params->get('se_survey_link', 0);

      if ( $seSurveyMenuId > 0 ) {
        $menu = $app->getMenu();
        $item = $menu->getItem($seSurveyMenuId);
        $surveyLink = Route::_($item->link);
      }
    }

    return (object) [
      'items' => $classes, // All classes
      'tabs' => $tab_items, // Organized by tab
      'categories' => $classCategories, // Categories used by overview listing
      'types' => $classTypes, // Class types of presentations
      'survey' => $surveyLink,
    ];
  }
}