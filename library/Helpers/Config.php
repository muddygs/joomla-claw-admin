<?php

namespace ClawCorpLib\Helpers;

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;

use ClawCorpLib\Enums\ConfigFieldNames;

class Config
{
  private DatabaseDriver $db;

  public function __construct(
    public readonly string $eventConfigAlias)
  {
    $this->db = Factory::getContainer()->get('DatabaseDriver');
  }

  /**
   * Returns config value
   * @param ConfigFieldNames $section Section name
   * @param string $key Key name
   * @return string Value or null if not found 
   */
  public function getConfigText(ConfigFieldNames $section, string $key): ?string
  {
    $query = $this->buildGetQuery($section, $key);
    $this->db->setQuery($query);
    return $this->db->loadResult();
  }

  /**
   * Returns an associative array of the "key=>text" values for a given config section
   * @param ConfigFieldNames $section fieldname to get values for
   * @return array List or null if not found 
   */
  public function getConfigValuesText(ConfigFieldNames $section): ?array
  {
    $query = $this->buildGetQuery($section);
    $this->db->setQuery($query);
    return $this->db->loadAssocList('value','text');
  }

  private function buildGetQuery(ConfigFieldNames $section, string $key = ''): \Joomla\Database\DatabaseQuery
  {
    $fieldName = $section->toString();
    $configAlias = $this->eventConfigAlias;

    $query = $this->db->getQuery(true);
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

    return $query;
  }
}
