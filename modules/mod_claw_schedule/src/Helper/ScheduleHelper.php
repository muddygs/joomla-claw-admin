<?php

/**
 * @package     CLAW.Module
 * @subpackage  mod_claw_schedule
 *
 * @copyright   (C) 2024 C.L.A.W. Corp.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Module\Schedule\Site\Helper;

use ClawCorpLib\Helpers\Schedule;
use ClawCorpLib\Lib\Aliases;
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

  public function loadSchedule(): array
  {
    $db = $this->getDatabase();
    // $debugDateTime = new \DateTime('2024-04-13 09:03:00');
    // $schedule = new Schedule(Aliases::current(true), $db, 'upcoming', $debugDateTime);
    $schedule = new Schedule(Aliases::current(true), $db, 'upcoming');
    return $schedule->getUpcomingEvents();
  }
}
