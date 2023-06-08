<?php
namespace ClawCorpLib\Helpers;

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;

class Locations {
  private static array $cache = [];

  public static function GetLocationsList(): array {
    if ( count(Locations::$cache) > 0) return Locations::$cache;

    $db = Factory::getContainer()->get('DatabaseDriver');

    $query = $db->getQuery(true);

    $query->select($db->qn(['id','value']))
    ->from($db->qn('#__claw_locations'))
    ->where($db->qn('published') . '=1');

    $db->setQuery($query);
    Locations::$cache = $db->loadObjectList('id') ?? [];
    return Locations::$cache;
  }

  public static function GetLocationById(int $id): ?object
  {
    if ( !count(Locations::$cache) ) Locations::GetLocationsList();
    return Locations::$cache[$id] ?? (object)['value' => ''];
  }
}