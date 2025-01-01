<?php

/**
 * @package     ClawCorpLib
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorpLib\Skills;

use ClawCorpLib\Enums\SkillOwnership;
use ClawCorpLib\Enums\SkillPublishedState;
use ClawCorpLib\Iterators\SkillArray;
use ClawCorpLib\Lib\EventInfo;
use Joomla\CMS\Factory;

\defined('JPATH_PLATFORM') or die;

final class Skills
{
  /**
   * Get all the classes for a given event
   * @param EventInfo $eventInfo
   * @param SkillPublishedState $published Defaults to any
   * @return SkillArray
   */
  public static function get(EventInfo $eventInfo, SkillPublishedState $published = SkillPublishedState::any): SkillArray
  {
    $skills = new SkillArray();

    $db = Factory::getContainer()->get('DatabaseDriver');
    $event = $eventInfo->alias;

    $query = $db->getQuery(true);
    $query->select('id')
      ->from(Skill::SKILLS_TABLE)
      ->where('event = :event')
      ->bind(':event', $event)
      ->order(['day', 'time_slot', 'title', 'id']);

    if ($published != SkillPublishedState::any) {
      $p = $published->value;
      $query->where('published = :published')
        ->bind(':published', $p);
    }

    $db->setQuery($query);
    $ids = $db->loadColumn();

    foreach ($ids as $id) {
      // Auto load from id
      $skill = new Skill(
        id: $id,
      );

      $skills[$id] = $skill;
    }

    return $skills;
  }

  /**
   * Get a list of classes regardless of ownership
   * @param EventInfo $eventInfo
   * @param int $pid Presenter ID
   * @return SkillArray
   */
  public static function getByPresenterId(EventInfo $eventInfo, int $pid): SkillArray
  {
    $skills = new SkillArray();

    $db = Factory::getContainer()->get('DatabaseDriver');
    $event = $eventInfo->alias;

    $query = $db->getQuery(true);
    $query->select('id')
      ->from(Skill::SKILLS_TABLE)
      ->where('event = :event')
      ->where('presenter_id = :pid')
      ->bind(':event', $event)
      ->bind(':pid', $pid)
      ->order(['id']);
    $db->setQuery($query);
    $ids = $db->loadColumn();

    foreach ($ids as $id) {
      // Auto load from id
      $skill = new Skill(
        id: $id,
      );

      $skills[$id] = $skill;
    }

    return $skills;
  }

  /**
   * Gets an array of skill classes with user ownership
   * @param int $uid User ID
   * @return SkillArray
   */
  public static function getByUid(int $uid): SkillArray
  {
    $skills = new SkillArray();

    $db = Factory::getContainer()->get('DatabaseDriver');
    $presenterArray = Presenter::getAllByUid($uid);
    $pids = $presenterArray->keys();

    if (!count($pids)) return $skills;

    $query = $db->getQuery(true);
    $query->select('id')
      ->from(Skill::SKILLS_TABLE)
      ->where('presenter_id IN (' . implode(',', $pids) . ')')
      ->where('(archive_state IS null OR archive_state=\'\')')
      ->where('ownership =' . SkillOwnership::user->value)
      ->order(['id']);
    $db->setQuery($query);
    $ids = $db->loadColumn();

    foreach ($ids as $id) {
      $skills[$id] = new Skill($id);
    }

    return $skills;
  }
}
