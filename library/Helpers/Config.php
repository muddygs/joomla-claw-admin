<?php

namespace ClawCorpLib\Helpers;

use ClawCorpLib\Enums\ConfigFieldNames;
use ClawCorpLib\Enums\EventTypes;
use ClawCorpLib\Lib\EventInfo;
use Joomla\CMS\Factory;

class Config
{
  // Cache of config values
  private static array $_titles = [];
  private static string $_current = '';

  public function __construct(
    public readonly string $eventConfigAlias)
  {
  }

  /**
   * Returns an object list (use ->value/->text) of values for a given config fieldname
   * @param ConfigFieldNames $section fieldname to get values for
   * @return array 
   */
  public function getColumn(ConfigFieldNames $section): array
  {
    $db = Factory::getContainer()->get('DatabaseDriver');
    $fieldName = $section->toString();

    $configAlias = $this->eventConfigAlias;

    $query = $db->getQuery(true);
    $query->select(['value', 'text'])
      ->from('#__claw_field_values')
      ->where('fieldname = :fieldname')
      ->where('event = :event')
      ->order('value')
      ->bind(':fieldname', $fieldName)
      ->bind(':event', $configAlias);
    
    $db->setQuery($query);
    return $db->loadObjectList('value');
  }

  /**
   * Returns an array of the "text" values for a given config fieldname
   * @param ConfigFieldNames $section fieldname to get values for
   * @param string $key (optional) if set, return only text for this value 
   * @return mixed array of "text" values or a single value if $key is set (false on db error) 
   */
  public function getConfigValuesText(ConfigFieldNames $section, string $key = ''): mixed
  {
    /** @var \Joomla\Database\DatabaseDriver */
    $db = Factory::getContainer()->get('DatabaseDriver');
    $fieldName = $section->toString();

    // Need a local variable for the bind to work
    $configAlias = $this->eventConfigAlias;

    $query = $db->getQuery(true);
    $query->select(['value','text'])
      ->from('#__claw_field_values')
      ->where('fieldname = :fieldname')
      ->where('event = :event')
      ->order('text')
      ->bind(':fieldname', $fieldName)
      ->bind(':event', $configAlias);

    if ($key != '') {
      $query->where('value = :value')
        ->bind(':value', $key);
    }
    $db->setQuery($query);
    $result = $key != '' ? $db->loadResult() : $db->loadAssocList('value','text');
    return $result;
  }

  // TODO: Move all these static functions to EventConfig class
  
  public static function getTitleMapping(): array
  {
    if ( count(self::$_titles)) return self::$_titles;

    $eventList = EventInfo::getEventInfos();
    $titles = [];

    /** @var \ClawCorpLib\Lib\EventInfo */
    foreach ( $eventList AS $alias => $eventInfo ) {
      if ( $eventInfo->eventType != EventTypes::main ) continue;
      $titles[$alias] = $eventInfo->description;
    }

    self::$_titles = $titles;
    return $titles;
  }
}
