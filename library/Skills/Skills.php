<?php

/**
 * @package     ClawCorpLib
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorpLib\Skills;

use ClawCorpLib\Iterators\SkillArray;
use ClawCorpLib\Lib\EventInfo;
use Joomla\CMS\Factory;

\defined('JPATH_PLATFORM') or die;

final class Skills
{
  public static function get(EventInfo $eventInfo): SkillArray
  {
    $skills = new SkillArray();

    $db = Factory::getContainer()->get('DatabaseDriver');
    $event = $eventInfo->alias;

    $query = $db->getQuery(true);
    $query->select('id')
      ->from(Skill::SKILLS_TABLE)
      ->where('event = :event')
      ->bind(':event', $event)
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
}
