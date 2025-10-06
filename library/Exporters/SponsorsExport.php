<?php

/**
 * @package     ClawCorpLib
 * @subpackage  com_claw
 *
 * @copyright   (C) 2025 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorpLib\Exporters;

use Joomla\CMS\Factory;
use ClawCorpLib\Helpers\Helpers;
use Joomla\CMS\Uri\Uri;
use ClawCorpLib\Lib\Sponsors;
use ClawCorpLib\Enums\EbPublishedState;

class SponsorsExport
{
  public static function toCSV(string $filename)
  {
    $root = Uri::getInstance();
    $root->setPath('/');
    $imageRoot = $root->root();

    $db = Factory::getContainer()->get('DatabaseDriver');

    // Load database columns
    $columnNames = array_keys($db->getTableColumns('#__claw_sponsors'));
    $sponsors = Sponsors::get(); // published

    /*
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `link` VARCHAR(255) NOT NULL,
    `type` TINYINT NOT NULL,
    `description` TEXT DEFAULT NULL,
    `logo_small` VARCHAR(255) NULL,
    `logo_large` VARCHAR(255) NULL,
    `published` TINYINT(4) NOT NULL DEFAULT '1',
    `ordering` INT(11) NULL DEFAULT NULL,
    `expires` DATE DEFAULT '0000-00-00',
    `mtime` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
     */

    $remapping = [
      'id' => 'Unique ID',
      'name' => 'Sponsor Name',
      'description' => 'About',
      'type' => 'Sponsor Level',
      'location' => 'Location',
      'link' => 'Website',
      'logo_large' => 'Photo URL',
    ];

    $headers = array_map(function ($x) use ($remapping) {
      return array_key_exists($x, $remapping) ? $remapping[$x] : $x;
    }, $columnNames);

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
    header("Pragma: public");
    set_time_limit(0);
    ini_set('error_reporting', E_NOTICE);

    $fp = fopen('php://output', 'wb');
    fputcsv($fp, $headers);

    /** @var \ClawCorpLib\Lib\Sponsor */
    foreach ($sponsors as $sponsor) {
      $row = [];
      foreach ($columnNames as $col) {
        switch ($col) {
          case 'mtime':
          case 'expires':
            $row[] = match ($sponsor->$col) {
              null => '',
              default => $sponsor->$col->toSql(),
            };
            break;
          case 'published':
            $row[] = match ($sponsor->published) {
              EbPublishedState::any => 'Unpublished',
              EbPublishedState::published => 'Published',
              default => 'Unknown',
            };
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
            $row[] = $sponsor->type->toString();
            break;
          default:
            $row[] = $sponsor->$col;
            break;
        }
      }
      fputcsv($fp, $row);
    }

    fclose($fp);
  }
}
