<?php

/**
 * @package     CLAW.Module
 * @subpackage  mod_claw_schedule
 *
 * @copyright   (C) 2024 C.L.A.W. Corp.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Module\Schedule\Site\Helper;

use ClawCorpLib\Iterators\ScheduleArray;
use ClawCorpLib\Lib\Schedule;
use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Lib\EventInfo;
use Joomla\Database\DatabaseAwareInterface;
use Joomla\Database\DatabaseAwareTrait;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Helper for mod_claw_schedule
 *
 */
class ScheduleHelper implements DatabaseAwareInterface
{
  use DatabaseAwareTrait;

  public function loadSchedule(): ScheduleArray
  {
    $eventInfo = new EventInfo(Aliases::current(true));
    $schedule = new Schedule($eventInfo, 'upcoming');
    return $schedule->scheduleArray;
  }
}
