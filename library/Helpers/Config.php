<?php

namespace ClawCorpLib\Helpers;

use ClawCorpLib\Enums\EventTypes;
use ClawCorpLib\Lib\EventInfo;
use Joomla\CMS\Factory;

class Config
{
  // Cache of config values
  private static array $_titles = [];
  private static string $_current = '';

  /**
   * Returns an object list (use ->value/->text) of values for a given config fieldname
   * @param string $section fieldname to get values for
   * @return array 
   */
  public static function getColumn(string $section): array
  {
    $db = Factory::getContainer()->get('DatabaseDriver');

    $query = $db->getQuery(true);
    $query->select(['value', 'text'])
      ->from('#__claw_field_values')
      ->where('fieldname = :fieldname')
      ->order('value')
      ->bind(':fieldname', $section);
    $db->setQuery($query);
    return $db->loadObjectList('value');
  }

  /**
   * Returns an array of the "text" values for a given config fieldname
   * @param string $section fieldname to get values for
   * @param string $key (optional) if set, return only text for this value 
   * @return mixed array of "text" values or a single value if $key is set (false on db error) 
   */
  public static function getConfigValuesText(string $section, string $key = ''): mixed
  {
    $db = Factory::getContainer()->get('DatabaseDriver');

    $query = $db->getQuery(true);
    $query->select(['text'])
      ->from('#__claw_field_values')
      ->where('fieldname = :fieldname')
      ->order('text')
      ->bind(':fieldname', $section);

    if ($key != '') {
      $query->where('value = :value')
        ->bind(':value', $key);
    }
    $db->setQuery($query);
    $result = $key != '' ? $db->loadResult() : $db->loadColumn();
    return $result;
  }

  public static function getTitleMapping(): array
  {
    if ( count(self::$_titles)) return self::$_titles;

    $eventList = EventInfo::getEventList();
    $titles = [];

    /** @var \ClawCorpLib\Lib\EventInfo */
    foreach ( $eventList AS $alias => $eventInfo ) {
      if ( $eventInfo->eventType != EventTypes::main ) continue;
      $titles[$alias] = $eventInfo->description;
    }

    self::$_titles = $titles;
    return $titles;
  }

  public static function getCurrentEventAlias(bool $next = false): string
  {
    if ( self::$_current != '' && !$next) return self::$_current;

    $eventList = EventInfo::getEventList();
    $nextAlias = '';

    if ( count($eventList) == 0 ) {
      die('No events found in Config::getCurrentEvent().');
    };

    $endDates = [];

    /** @var \ClawCorpLib\Lib\EventInfo */
    foreach ( $eventList AS $alias => $eventInfo ) {
      if ( $eventInfo->eventType != EventTypes::main ) continue;
      
      $endDates[$eventInfo->end_date->toSql()] = $alias;
    }

    // Find earliest event that has not ended
    
    ksort($endDates);

    $now = Factory::getDate()->toSql();

    foreach ( array_keys($endDates) AS $endDate ) {
      if ( $endDate > $now ) {
        if ( $next ) {
          if ( $nextAlias == '' ) {
            $nextAlias = $endDates[$endDate];
            continue;
          } else {
            return $endDates[$endDate];
          }
        }

        self::$_current = $endDates[$endDate];
        break;
      }
    }

    if ( self::$_current == '' ) {
      // Failsafe-ish: Get last item in array
      self::$_current = array_pop($endDates);
    }

    if ( self::$_current == '' ) {
      die('No '. $next ? 'next' : 'current' . ' event found in Config::getCurrentEvent().');
    }

    return self::$_current;
  }

  public static function getNextEventAlias()
  {
    return self::getCurrentEventAlias(true);
  }

  public static function getActiveEventAliases(bool $mainOnly = false): array
  {
    $eventList = EventInfo::getEventList();
    /** @var \ClawCorpLib\Lib\EventInfo */
    foreach ( $eventList AS $alias => $eventInfo ) {
      if ( $mainOnly && $eventInfo->eventType != EventTypes::main ) {
        unset($eventList[$alias]);
      }
    }
    return array_keys($eventList);
  }
}
