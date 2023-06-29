<?php
namespace ClawCorpLib\Helpers;

use Joomla\CMS\Factory;

class Sponsors {
  private array $cache = [];

  public function __construct()
  {
    $this->CacheSponsorsList();
  }

  private function CacheSponsorsList()
  {
    if ( count($this->cache) > 0) return $this->cache;
    
    $db = Factory::getContainer()->get('DatabaseDriver');

    $query = $db->getQuery(true);

    $query->select('*')
    ->from($db->qn('#__claw_sponsors'))
    ->where($db->qn('published') . '=1');

    $db->setQuery($query);
    $this->cache = $db->loadObjectList('id') ?? [];
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
}