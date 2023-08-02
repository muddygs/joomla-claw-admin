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
use Joomla\CMS\Factory;

use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Helpers\Skills;
use ClawCorpLib\Lib\ClawEvents;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Methods to handle public class listing.
 */
class SkillslistModel extends BaseDatabaseModel
{
  public function GetConsolidatedList(string $event = Aliases::current): object
  {
    $db = $this->getDatabase();
    $presenters = Skills::GetPresenterList($db, $event);
    $classes = Skills::GetClassList($db, $event);

    $classTypes = Helpers::getClawFieldValues($this->getDatabase(), 'skill_class_type');
    $classCategories = Helpers::getClawFieldValues($this->getDatabase(), 'skill_category');

    // Prepare data for summary view

    $simple_items = [];

    foreach ( $classCategories AS $category ) {
      $simple_items[$category->value] = [
        'name' => $category->text,
        'ids' => [],
      ];
    }

    foreach ( $classes AS $class ) {
      if ( $class->published != 1 ) continue;

      $day = Helpers::dateToDayNum($class->day);
      $class->day_text = Helpers::dateToDay($class->day);
      $time = $class->time_slot;
      $title = Helpers::StringCleanup($class->title, true);

      $ordering = implode('-', [$day, $time, $title, $class->id]);

      if ( array_key_exists($class->category, $simple_items) ) {
        $simple_items[$class->category]['ids'][$ordering] = $class->id;
      }

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

    return (object) [
      'items' => $classes,
      'simple' => $simple_items,
      'categories' => $classCategories,
      'types' => $classTypes,
      'simple_items' => $simple_items,
    ];
  }

  public function GetPresenter(int $uid, string $event = Aliases::current): object
  {
    $db = $this->getDatabase();
    $presenter = Skills::GetPresenter($db, $uid, $event);

    return $presenter;
  }

  // private function namesort($a, $b) {}

  private function build_sorter($key) {
    return function ($a, $b) use ($key) {
        return strnatcmp($a[$key], $b[$key]);
    };
  }

  public function GetEventInfo(string $alias = Aliases::current) : \ClawCorpLib\Lib\EventInfo
  {
    $events = new ClawEvents(clawEventAlias: $alias);

    $info = $events->getClawEventInfo();
    return $info;
  }
}