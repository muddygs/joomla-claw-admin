<?php

namespace ClawCorpLib\Helpers;

use ClawCorpLib\Lib\ClawEvents;
use DateTime;
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

    $eventList = ClawEvents::GetEventList();
    $titles = [];

    foreach ( $eventList AS $alias => $eventInfo ) {
      if ( !$eventInfo->active || !$eventInfo->mainAllowed ) continue;
      $titles[$alias] = $eventInfo->description;
    }

    self::$_titles = $titles;
    return $titles;
  }

  public static function getCurrentEventAlias(): string
  {
    if ( self::$_current != '' ) return self::$_current;

    $eventList = ClawEvents::GetEventList();

    if ( count($eventList) == 0 ) {
      die('No events found in Config::getCurrentEvent().');
    };

    $endDates = [];

    foreach ( $eventList AS $alias => $eventInfo ) {
      if ( !$eventInfo->active || !$eventInfo->mainAllowed ) continue;
      
      $endDates[$eventInfo->end_date] = $alias;
    }

    // Find earliest event that has not ended
    
    ksort($endDates);

    foreach ( array_keys($endDates) AS $endDate ) {
      // TODO: Use database -> sql date
      if ( $endDate > date('Y-m-d hh:mm:ss') ) {
        self::$_current = $endDates[$endDate];
        break;
      }
    }

    if ( self::$_current == '' ) {
      // Failsafe-ish: Get last item in array
      self::$_current = array_pop($endDates);
    }

    return self::$_current;
  }

  public static function getActiveEventAliases(bool $mainOnly = false): array
  {
    $eventList = ClawEvents::GetEventList();
    foreach ( $eventList AS $alias => $eventInfo ) {
      if ( !$eventInfo->active ) {
        unset($eventList[$alias]);
      }
      if ( $mainOnly && !$eventInfo->mainAllowed ) {
        unset($eventList[$alias]);
      }
    }
    return array_keys($eventList);
  }
}
