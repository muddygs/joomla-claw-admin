<?php

/**
 * @package     ClawCorpLib
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorpLib\Lib;

use InvalidArgumentException;
use Joomla\CMS\Factory;

\defined('_JEXEC') or die;

class Ebfield
{
  public int $id = 0;
  private object $field;

  public function __construct(
    public string $name
  ) {
    $this->field = (object)[];
    $this->loadField();

    if (0 == $this->id) {
      throw new InvalidArgumentException('Field name not found: ' . $this->name);
    }
  }

  private function loadField()
  {
    $db = Factory::getContainer()->get('DatabaseDriver');
    $query = $db->getQuery(true);
    $query
      ->select('*')
      ->from('#__eb_fields')
      ->where($db->quoteName('name') . ' = ' . $db->quote($this->name));
    $db->setQuery($query);
    $field = $db->loadObject();

    if ($field) {
      $this->id = $field->id;
      $this->field = $field;
    }
  }

  public function get(string $property)
  {
    if (property_exists($this->field, $property)) {
      return $this->field->$property;
    }

    throw new InvalidArgumentException('Field property not found: ' . $property);
  }

  /**
   * Returns count of field values used for a given event. Key: field_value, Value: value_count
   * @param int $eventId 
   * @return null|array 
   * @throws InvalidActionException 
   * @throws KeyNotFoundException 
   */
  public function valueCounts(int $eventId): ?array
  {
    if (!in_array($this->field->fieldtype, ['List', 'Checkboxes'])) {
      throw new InvalidArgumentException('Field type not supported: ' . $this->field->fieldtype);
    }

    $db = Factory::getContainer()->get('DatabaseDriver');
    $query = $db->getQuery(true);
    $query
      ->select('fv.field_value, COUNT(*) as value_count')
      ->from('#__eb_field_values AS fv')
      ->join('INNER', $db->qn('#__eb_registrants', 'r'), 'fv.registrant_id = r.id AND r.event_id=' . $eventId)
      ->where('r.published = 1')
      ->where('fv.field_id = ' . $this->id)
      ->group('fv.field_value');
    $db->setQuery($query);
    $fieldValues = $db->loadObjectList('field_value');

    return $fieldValues;
  }
}

