<?php

/**
 * @package     ClawCorpLib
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorpLib\Helpers;

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;

use ClawCorpLib\Enums\ConfigFieldNames;
use Joomla\CMS\Component\ComponentHelper;

class Config
{
  private DatabaseDriver $db;

  public function __construct(
    public readonly string $eventConfigAlias
  ) {
    $this->db = Factory::getContainer()->get('DatabaseDriver');
  }

  /**
   * Returns config value
   * @param ConfigFieldNames $section Section name
   * @param string $key Key name
   * @param string $default Default value
   * @return string Value or null if not found 
   */
  public function getConfigText(ConfigFieldNames $section, string $key, string $default): string
  {
    $query = $this->buildGetQuery($section, $key);
    $this->db->setQuery($query);
    $row = $this->db->loadObject();

    if (!is_null($row)) {
      return $row->text;
    }

    return $default;
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
    return $this->db->loadAssocList('value', 'text');
  }

  private function buildGetQuery(ConfigFieldNames $section, string $key = ''): \Joomla\Database\DatabaseQuery
  {
    $fieldName = $section->toString();
    $configAlias = $this->eventConfigAlias;

    $query = $this->db->getQuery(true);
    $query->select(['value', 'text'])
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

  public static function getGlobalConfig(string $key = '', $default = null): array|string
  {
    $comclaw = ComponentHelper::getParams('com_claw');

    if ($key) {
      $result = $comclaw->get($key, $default);
      return is_null($result) ? '' : $result;
    }

    return $comclaw->toArray();
  }
}
