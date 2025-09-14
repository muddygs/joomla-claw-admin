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

use ClawCorpLib\Lib\Sponsors;
use ClawCorpLib\Helpers\Helpers;
use Joomla\CMS\HTML\HTMLHelper;

$filename = 'Sponsors_Export_' . HtmlHelper::date('now', 'Y-m-d_H-i-s') . '.csv';

// Load database columns
$columnNames = array_keys($this->db->getTableColumns('#__claw_sponsors'));
$sponsors = new Sponsors(published: true);

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
header("Pragma: public");
ob_clean();
ob_start();
set_time_limit(0);
ini_set('error_reporting', E_NOTICE);

$fp = fopen('php://output', 'wb');
fputcsv($fp, $columnNames);

foreach ($sponsors->sponsors as $sponsor) {
  $row = [];
  foreach ($columnNames as $col) {
    switch ($col) {
      case 'mtime':
      case 'expires':
        $row[] = match ($sponsor->$col) {
          null => '',
          default => $sponsor->$col->format('Y-m-d H:i:s'),
        };
        break;
      case 'published':
        $row[] = $sponsor->$col->value;
        break;
      case 'id':
        $row[] = 'sponsor_' . $sponsor->$col;
        break;
      case 'logo_small':
      case 'logo_large':
        $link = Helpers::convertMediaManagerUrl($sponsor->$col);
        $row[] = is_null($link) ? '' : $link;
        break;
      case 'type':
        $row[] = $sponsor->$col->toString();
        break;
      default:
        $row[] = $sponsor->$col;
        break;
    }
  }
  fputcsv($fp, $row);
}

fclose($fp);
ob_end_flush();
