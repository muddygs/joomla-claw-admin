<?php
namespace ClawCorpLib\Helpers;

use Joomla\Database\DatabaseDriver;

class Locations {
  private static array $cache = [];

  public static function GetLocationsList(DatabaseDriver $db): array {
    if ( count(Locations::$cache) > 0) return Locations::$cache;

    $query = $db->getQuery(true);

    $query->select($db->qn(['id','value']))
    ->from($db->qn('#__claw_locations'))
    ->where($db->qn('published') . '=1');

    $db->setQuery($query);
    Locations::$cache = $db->loadObjectList('id') ?? [];
    return Locations::$cache;
  }
}