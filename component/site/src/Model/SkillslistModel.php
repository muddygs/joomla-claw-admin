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

use ClawCorpLib\Helpers\Helpers;

use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Helpers\Config;
use ClawCorpLib\Helpers\Skills;
use ClawCorpLib\Lib\ClawEvents;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Methods to handle public class listing.
 */
class SkillslistModel extends BaseDatabaseModel
{
  public function GetConsolidatedList(string $eventAlias): object
  {
    $db = $this->getDatabase();
    $presenters = Skills::GetPresenterList($db, $eventAlias);
    $classes = Skills::GetClassList($db, $eventAlias);

    $classTypes = Config::getColumn('skill_class_type');
    $classCategories = Config::getColumn('skill_category');

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

      // These should all be defined per validation on a published skills class
      $day = Helpers::dateToDayNum($class->day);
      $class->day_text = Helpers::dateToDay($class->day);
      $time = $class->time_slot;
      $title = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $class->title));

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
      $copresenters = explode(',', $class->presenters);

      if ( $copresenters[0] !== '' ) {
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

    return (object) [
      'items' => $classes, // All classes
      'tabs' => $tab_items, // Organized by tab
      'categories' => $classCategories, // Categories used by overview listing
      'types' => $classTypes, // Class types of presentations
    ];
  }

  public function GetPresenter(int $uid, string $event): object
  {
    $db = $this->getDatabase();
    $presenter = Skills::GetPresenter($db, $uid, $event);

    return $presenter;
  }
}