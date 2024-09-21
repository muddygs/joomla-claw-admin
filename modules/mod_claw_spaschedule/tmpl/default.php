<?php

/**
 * @package     CLAW.Schedule
 * @subpackage  mod_claw_spaschedule
 *
 * @copyright   (C) 2024 C.L.A.W. Corp.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use ClawCorpLib\Helpers\Bootstrap;
use Joomla\CMS\Helper\ModuleHelper;


$tabs = ['Fri', 'Sat', 'Sun'];
$content = [];

$path  = ModuleHelper::getLayoutPath('mod_claw_spaschedule', 'default_day');

ob_start();
/** @var \ClawCorpLib\Lib\EventConfig $eventConfig */
$dayStart = $eventConfig->eventInfo->modify('Fri 00:00')->toUnix();
$dayEnd = $eventConfig->eventInfo->modify('Fri 23:59')->toUnix();

include $path;
$content[] = ob_get_clean();

ob_start();
$dayStart = $eventConfig->eventInfo->modify('Sat 00:00')->toUnix();
$dayEnd = $eventConfig->eventInfo->modify('Sat 23:59')->toUnix();
include $path;
$content[] = ob_get_clean();

ob_start();
$dayStart = $eventConfig->eventInfo->modify('Sun 00:00')->toUnix();
$dayEnd = $eventConfig->eventInfo->modify('Sun 23:59')->toUnix();
include $path;
$content[] = ob_get_clean();

Bootstrap::writePillTabs($tabs, $content);
