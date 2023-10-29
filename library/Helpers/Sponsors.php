<?php
namespace ClawCorpLib\Helpers;

use ClawCorpLib\Enums\SponsorshipType;
use InvalidArgumentException;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\Exception\UnsupportedAdapterException;
use Joomla\Database\Exception\QueryTypeAlreadyDefinedException;
use RuntimeException;

class Sponsors {
  private array $cache = [];
  private DatabaseDriver $db;

  public function __construct()
  {
    $this->db = Factory::getContainer()->get('DatabaseDriver');
    $this->CacheSponsorsList();
  }

  private function CacheSponsorsList()
  {
    if ( count($this->cache) > 0) return $this->cache;

    $ordering = SponsorshipType::valuesOrdered();

    $query = $this->db->getQuery(true);

    $query->select('*')
    ->from($this->db->qn('#__claw_sponsors'))
    ->where($this->db->qn('published') . '=1')
    ->order('FIND_IN_SET(' . $this->db->qn('type') . ', ' . $this->db->q(implode(',', $ordering)) . ')')
    ->order($this->db->qn('ordering') . ' ASC')
    ->order($this->db->qn('name') . ' ASC');

    $this->db->setQuery($query);
    $this->cache = $this->db->loadObjectList('id') ?? [];
  }

  public function GetSponsorsList(): array
  {
    if ( !count($this->cache) ) Sponsors::CacheSponsorsList();
    return $this->cache;
  }

  public function GetSponsorById(int $id): ?object
  {
    if ( !count($this->cache) ) Sponsors::CacheSponsorsList();
    return array_key_exists($id, $this->cache) ? $this->cache[$id] : null;
  }

  public function GetSmallImageLink(int $id): string
  {
    if ( !count($this->cache) ) Sponsors::CacheSponsorsList();

    $sponsor = array_key_exists($id, $this->cache) ? $this->cache[$id] : '';

    if (empty($sponsor)) return '';

    $tag = '';

    $link = $sponsor->link ?? '';

    if (!empty($link)) {
        $tag = "<a href=\"$link\" alt=\"{$sponsor->name}\" title=\"{$sponsor->name}\" target=\"_blank\">";
    }

    // images/0_static_graphics/sponsors/100/abuniverse.jpg#joomlaImage://local-images/0_static_graphics/sponsors/100/abuniverse.jpg?width=100&height=100
    $tag = $tag . "<img src=\"{$sponsor->logo_small}\" class=\"img-fluid mx-auto\"/>";

    if (!empty($link)) {
        $tag = $tag . '</a>';
    }

    return $tag;

  }

  /**
   * Returns array of published sponsors, potentially filtered by sponsor types
   * @param DatabaseDriver $db 
   * @param array $filter Sponsor types
   * @return array 
   * @throws UnsupportedAdapterException 
   * @throws QueryTypeAlreadyDefinedException 
   * @throws RuntimeException 
   * @throws InvalidArgumentException 
   */
  static public function GetPublishedSponsors(DatabaseDriver $db, array $filter = []): array
  {
    $query = $db->getQuery(true);
    $query->select($db->qn(['id', 'name']))
      ->from($db->qn('#__claw_sponsors'))
      ->where($db->qn('published') . '=1');

    if (sizeof($filter) > 0) {
      $filter = (array)($db->q($filter));
      $query->where($db->qn('type') . ' IN (' . implode(',', $filter) . ')');
    }

    $query->order('name ASC');

    $db->setQuery($query);
    return $db->loadObjectList();
  }

  public function toCSV(string $filename)
  {
    // Load database columns
    $columnNames = array_keys($this->db->getTableColumns('#__claw_sponsors'));

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="'. $filename . '"');
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
    header("Pragma: public");
    ob_clean();
    ob_start();
    set_time_limit(0);
    ini_set('error_reporting', E_NOTICE);

    $fp = fopen('php://output', 'wb');
    fputcsv($fp, $columnNames);

    foreach ( $this->cache AS $c) {
      $row = [];
      foreach ( $columnNames AS $col ) {
        switch ($col) {
          case 'id':
            $row[] = 'sponsor_'.$c->$col;
            break;
          case 'logo_small':
          case 'logo_large':
            $link = Helpers::convertMediaManagerUrl($c->$col);
            $row[] = is_null($link) ? '' : $link;
            break;
          case 'type':
            $row[] = SponsorshipType::FindValue($c->$col)->toString();
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