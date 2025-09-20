<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2025 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
\defined('_JEXEC') or die('Restricted Access');

use ClawCorpLib\Helpers\ScheduleExport;
use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Lib\EventInfo;
use Joomla\CMS\HTML\HTMLHelper;

$eventInfo = new EventInfo(Aliases::current(true));
$filename = 'Schedule_Export_' . HtmlHelper::date('now', 'Y-m-d_H-i-s') . '.csv';
ScheduleExport::toCSV($eventInfo, $filename);
