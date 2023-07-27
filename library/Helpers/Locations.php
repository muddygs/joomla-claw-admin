<?php
namespace ClawCorpLib\Helpers;

use Joomla\CMS\Factory;

class Locations {
  private static array $cache = [];

  public static int $blankLocation = -1;

  public static function GetLocationsList(string $parentAlias = ''): array {
    if ( $parentAlias == '' ) $parentAlias = '_all_';
    if ( array_key_exists($parentAlias, Locations::$cache) ) return Locations::$cache[$parentAlias];

    $db = Factory::getContainer()->get('DatabaseDriver');

    $query = $db->getQuery(true);

    $query->select($db->qn(['id','value']))
    ->from($db->qn('#__claw_locations'))
    ->where($db->qn('published') . '=1')
    ->where($db->qn('catid'). '=0')
    ->order($db->qn('value'));

    if ( $parentAlias != '' && $parentAlias != '_all_') {
      $query->where($db->qn('alias') . '=' . $db->q($parentAlias));
    }

    $db->setQuery($query);
    $parents = $db->loadObjectList('id') ?? [];

    foreach ( $parents AS $p) {
      // push on the parent
      Locations::$cache[$p->id] = $p;

      // get the children
      $query = $db->getQuery(true);
      $query->select($db->qn(['id','value']))
      ->from($db->qn('#__claw_locations'))
      ->where($db->qn('published') . '=1')
      ->where($db->qn('catid'). '=' . $p->id)
      ->order($db->qn('value'));

      $db->setQuery($query);
      $children = $db->loadObjectList('id') ?? [];

      if ( !array_key_exists($parentAlias, Locations::$cache) ) Locations::$cache[$parentAlias] = [];
      Locations::$cache[$parentAlias] = array_merge(Locations::$cache[$parentAlias], $children);
    }

    return Locations::$cache[$parentAlias];
  }

  // TODO: Need parents, and this is a quickie to get that info for now
  public static function GetRootLocationIds()
  {
    $db = Factory::getContainer()->get('DatabaseDriver');

    $query = $db->getQuery(true);

    $query->select($db->qn(['id']))
    ->from($db->qn('#__claw_locations'))
    ->where($db->qn('published') . '=1')
    ->where($db->qn('catid'). '=0');

    $db->setQuery($query);
    return $db->loadColumn() ?? [];
  }

  /* OLDER ID ORDER */
  // static public function getLocations(DatabaseDriver $db, string $baseAlias = ''): array
  // {
  //   $query = $db->getQuery(true);
  //   $query->select(['l.id', 'l.value', 'l.catid'])
  //     ->from($db->qn('#__claw_locations', 'l'));

  //   if ($baseAlias != '') {
  //     $query->join('LEFT OUTER', $db->qn('#__claw_locations', 't') . ' ON ' . $db->qn('t.alias') . ' = ' . $db->q($baseAlias))
  //       ->where($db->qn('t.published') . '= 1')
  //       ->where($db->qn('l.catid') . '=' . $db->qn('t.id'));
  //   }

  //   $query->where($db->qn('l.published') . '= 1');
  //   $query->order('l.catid ASC, l.ordering ASC');

  //   $db->setQuery($query);
  //   return $db->loadObjectList();
  // }

  public static function GetLocationById(int $id): ?object
  {
    if ( $id == Locations::$blankLocation ) return (object)['value' => ''];
    if ( !count(Locations::$cache) ) Locations::GetLocationsList();
    return Locations::$cache[$id] ?? (object)['value' => ''];
  }

  /**
   * Validates if an location alias is defined in eventbooking
   * @param string $alias
   * @return bool True if alias is found
   */
  public static function ValidateLocationAlias(string $alias): bool
  {
    $db = Factory::getContainer()->get('DatabaseDriver');
    $query = $db->getQuery(true)
      ->select('COUNT(*)')
      ->from('#__claw_locations')
      ->where('published = 1')
      ->where('alias = :alias')
      ->bind(':alias', $alias);
    $db->setQuery($query);
    return $db->loadResult() > 0;
  }
}