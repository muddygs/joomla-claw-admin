<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
\defined('_JEXEC') or die('Restricted Access');

use ClawCorpLib\Exporters\SponsorsExport;
use Joomla\CMS\HTML\HTMLHelper;

$filename = 'Sponsors_Export_' . HtmlHelper::date('now', 'Y-m-d_H-i-s') . '.csv';
SponsorsExport::toCSV($filename);
