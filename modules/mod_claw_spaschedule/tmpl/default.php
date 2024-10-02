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

$path = ModuleHelper::getLayoutPath('mod_claw_spaschedule', 'default_day');
$tabs = [];
$content = [];

foreach ($days as $day => $count) {
  if ($count > 0) {
    $tabs[] = $day;

    ob_start();
    /** @var \ClawCorpLib\Lib\EventConfig $eventConfig */
    $dayStart = $eventConfig->eventInfo->modify($day . ' 00:00')->toUnix();
    $dayEnd = $eventConfig->eventInfo->modify($day . ' 23:59')->toUnix();

    include $path;
    $content[] = ob_get_clean();
  }
}

if (count($tabs)) {
  Bootstrap::writePillTabs($tabs, $content);
} else {
?>
  No sessions available for reservation.
<?php
}
