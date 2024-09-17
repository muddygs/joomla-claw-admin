<?php

/**
 * @package     CLAW.Sponsors
 * @subpackage  mod_claw_sponsors
 *
 * @copyright   (C) 2023 C.L.A.W. Corp.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorpLib\Helpers;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;

/**
 * Helper for mod_claw_sponsors
 */
class Vendors
{
  private DatabaseDriver $db;
  private array $cache = [];

  public function __construct(
    public string $eventAlias
  ) {
    $this->db = Factory::getContainer()->get('DatabaseDriver');
    $this->loadVendors();
  }

  private function loadVendors()
  {
    $query = $this->db->getQuery(true);

    $query->select('*')
      ->from('#__claw_vendors')
      ->where('published = 1')
      ->where('event = ' . $this->db->quote($this->eventAlias))
      ->order('ordering');

    $this->db->setQuery($query);
    $results = $this->db->loadObjectList();
    if (!is_null($results)) $this->cache = $results;
  }

  public function toCSV(string $filename)
  {
    // Load database columns
    $columnNames = array_keys($this->db->getTableColumns('#__claw_vendors'));

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
    ob_clean();
    ob_start();
    set_time_limit(0);
    ini_set('error_reporting', E_NOTICE);

    $fp = fopen('php://output', 'wb');
    fputcsv($fp, $columnNames);

    foreach ($this->cache as $c) {
      $row = [];
      foreach ($columnNames as $col) {
        switch ($col) {
          case 'id':
            $row[] = 'vendor_' . $c->$col;
            break;
          case 'logo':
            $link = Helpers::convertMediaManagerUrl($c->$col);
            $row[] = is_null($link) ? '' : $link;
            break;
          default:
            $row[] = $c->$col;
            break;
        }
      }
      fputcsv($fp, $row);
    }

    fclose($fp);
    ob_end_flush();
  }
}
