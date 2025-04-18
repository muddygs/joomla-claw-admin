<?php

/**
 * @package     ClawCorpLib
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorpLib\Skills;

use ClawCorpLib\Iterators\PresenterArray;
use ClawCorpLib\Lib\EventInfo;
use ClawCorpLib\Enums\SkillPublishedState;
use Joomla\CMS\Factory;

\defined('JPATH_PLATFORM') or die;

final class Presenters
{
  /**
   * Get the list of presenters for a event
   * @param EventInfo $eventInfo Desired event information
   * @param bool $publishedOnly Default false
   * @param string $order DB column for the sort (default 'id asc')
   */
  public static function get(EventInfo $eventInfo, SkillPublishedState $published = SkillPublishedState::any, string $order = 'id'): PresenterArray
  {
    $presenters = new PresenterArray();

    $db = Factory::getContainer()->get('DatabaseDriver');
    $event = $eventInfo->alias;

    $query = $db->getQuery(true);
    $query->select('id')
      ->from(Presenter::PRESENTERS_TABLE)
      ->where('event = :event')
      ->bind(':event', $event)
      ->order($order);
    if (SkillPublishedState::any != $published) {
      $query->where('published = ' . $published->value);
    }

    $db->setQuery($query);
    $ids = $db->loadColumn();

    foreach ($ids as $id) {
      // Auto load from id
      $presenter = new Presenter(
        id: $id,
      );

      $presenters[$id] = $presenter;
    }

    return $presenters;
  }

  /**
   * Get the list of presenters for a single Skill class
   * @
   */
  public static function bySkill(Skill $skill): PresenterArray
  {
    $presenters = new PresenterArray();

    $presentedIds = [$skill->presenter_id, ...$skill->other_presenter_ids];

    foreach ($presentedIds as $pid) {
      $presenter = new Presenter(
        id: $pid,
      );

      $presenters[$pid] = $presenter;
    }

    return $presenters;
  }
}
