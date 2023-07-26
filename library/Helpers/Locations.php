<?php
namespace ClawCorpLib\Helpers;

use Joomla\CMS\Factory;

class Locations {
  private static array $cache = [];

  public static int $blankLocation = -1;

  public static function GetLocationsList(string $parentAlias = ''): array {
    if ( count(Locations::$cache) > 0) return Locations::$cache;

    $db = Factory::getContainer()->get('DatabaseDriver');

    $query = $db->getQuery(true);

    $query->select($db->qn(['id','value']))
    ->from($db->qn('#__claw_locations'))
    ->where($db->qn('published') . '=1')
    ->where($db->qn('catid'). '=0')
    ->order($db->qn('value'));

    if ( $parentAlias != '' ) {
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

      Locations::$cache = array_merge(Locations::$cache, $children);

      // foreach ( $children AS $c) {
      //   // push on the child
      //   Locations::$cache[$c->id] = $c;
      // }
    }

    return Locations::$cache;
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
}