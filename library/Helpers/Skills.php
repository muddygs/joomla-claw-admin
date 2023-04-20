<?php
namespace ClawCorpLib\Helpers;

use Joomla\Database\DatabaseDriver;

class Skills {
  private static array $cache = [];

  public static function GetPresentersList(DatabaseDriver $db): array {
    if ( count(Skills::$cache)) return Skills::$cache;

    $query = $db->getQuery(true);

    $query->select($db->qn(['id','name']))
    ->from($db->qn('#__claw_presenters'))
    ->where($db->qn('published') . '=1')
    ->order('name ASC');

    $db->setQuery($query);
    Skills::$cache = $db->loadObjectList('id') ?? [];
    return Skills::$cache;
  }
}