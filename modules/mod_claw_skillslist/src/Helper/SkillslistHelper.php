<?php

/**
 * @package     CLAW.Schedule
 * @subpackage  mod_claw_skillslist
 *
 * @copyright   (C) 2024 C.L.A.W. Corp.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Module\Skillslist\Site\Helper;

use Joomla\Database\DatabaseAwareInterface;
use Joomla\Database\DatabaseAwareTrait;
use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Enums\EbPublishedState;
use ClawCorpLib\Helpers\Locations;
use ClawCorpLib\Lib\EventInfo;
use Joomla\CMS\Date\Date;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Helper for mod_claw_skillslist
 *
 */
class SkillslistHelper implements DatabaseAwareInterface
{
  use DatabaseAwareTrait;

  public function GetClassListToday(): array
  {
    $eventInfo = new EventInfo(Aliases::current(true));
    $startDate = new Date('now', $eventInfo->timezone);

    $eventStartDate = new Date($eventInfo->start_date, $eventInfo->timezone);
    
    // Check event start date. If it's in the future, use that as basis instead
    if ( $startDate < $eventStartDate ) {
      $startDate = $eventStartDate;
      $startDate->modify('Friday');
    }

    // $startDate = new Date('2024-04-13 09:33:00', 'America/New_York');
    $d = $startDate->format('Y-m-d');

    /** @var \Joomla\Database\DatabaseDriver */
    $db = $this->getDatabase();
    $eventAlias = Aliases::current(true);

    $query = $db->getQuery(true);
    $query->select(['id', 'title', 'location'])
      ->select('CONCAT(day, " ", INSERT(SUBSTRING_INDEX(time_slot, ":", 1), 3, 0, ":")) AS start_time')
      ->select('CONCAT(day, " ", INSERT(SUBSTRING_INDEX(time_slot, ":", 1), 3, 0, ":")) + INTERVAL CAST(SUBSTRING_INDEX(time_slot, ":", -1) AS SIGNED) MINUTE AS end_time')
      ->from($db->qn('#__claw_skills'))
      ->where('event = :event')->bind(':event', $eventAlias)
      ->where($db->qn('published') . ' = ' . EbPublishedState::published->value)
      ->where('day = ' . $db->q($d))
      ->order(['day', 'time_slot', 'title']);

    $db->setQuery($query);
    $classes = $db->loadObjectList('id') ?? [];

    $result = [
      'classes' => $classes,
      'locations' => Locations::getLocationsList(), 
      'timestamp' => $startDate->toUnix(),
    ];

    return $result;
  }
}

