<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
\defined('_JEXEC') or die('Restricted Access');

use ClawCorpLib\Helpers\SkillsExport;
use ClawCorpLib\Lib\Aliases;
use Joomla\CMS\HTML\HTMLHelper;

$skills = new SkillsExport($this->db, Aliases::current());
$filename = 'Classes_Export_' . HtmlHelper::date('now', 'Y-m-d_H-i-s') . '.csv';
$skills->classesCSV($filename, $this->publishedOnly);
