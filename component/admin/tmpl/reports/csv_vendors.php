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

use ClawCorpLib\Helpers\Vendors;
use ClawCorpLib\Lib\Aliases;
use Joomla\CMS\HTML\HTMLHelper;

$vendors = new Vendors(Aliases::current(true));
$filename = 'Vendors_Export_' . HtmlHelper::date('now', 'Y-m-d_H-i-s') . '.csv';
$vendors->toCSV($filename);
