<?php

namespace ClawCorp\Component\Claw\Administrator\Field;

use Joomla\CMS\Form\Field\ListField;
use ClawCorpLib\Helpers\Helpers;

defined('_JEXEC') or die;

class LocationListField extends ListField
{
  protected $type = "LocationList";
  private $maxValue = 2147483647; // SQL INT(11) max value signed int32

  private $listItems = [];

  /**
   * Method to get the field input markup for a generic list.
   * Use the multiple attribute to enable multiselect.
   *
   * @return  string  The field input markup.
   *
   * @since   3.7.0
   */
  protected function getInput()
  {
    $data = $this->getLayoutData();

    $data['options'] = (array) $this->getOptions();

    // VALID for other menus -- future reference $db = $this->getDatabase();
    $currentValue = $this->__get('value');
    if ($currentValue === '') {
      $data['value'] = $this->maxValue;
    }

    return $this->getRenderer($this->layout)->render($data);
  }


  /**
   * Customized method to populate the field option groups.
   *
   * @return  array  The field option objects as a nested array in groups.
   */
  protected function getOptions()
  {
    $this->listItems = parent::getOptions();

    $tbd[] = (object)[
      'id' => $this->maxValue,
      'value' => 'TBD',
      'catid' => 0
    ];

    $locations = array_merge($tbd, Helpers::getLocations($this->getDatabase()));
    $index = array_column($locations, 'catid', 'id');
    $titles = array_column($locations, 'value', 'id');

    $currentValue = $this->__get('value');

    foreach ($locations as $l) {
      if ($l->catid == 0) {
        $this->listItems[] = (object)[
          'value'    => $l->id,
          'text'     => $titles[$l->id],
          'disable'  => false,
          'class'    => '',
          'selected' => $l->id == $currentValue ? true : false,
          'checked'  => $l->id == $currentValue ? true : false,
          'onclick'  => '',
          'onchange' => ''
        ];

        $this->addChildren($index, $titles, $l->id, 1);

        // If nothing, set as option
        continue;
      }

      break; // Once non zero reached, base locations have been exhausted per locations query
    }

    return $this->listItems;
  }

  private function addChildren(&$index, &$titles, int $parent_id, int $level)
  {
    // Are there children? If not, return option
    $children_keys = array_keys($index, $parent_id, true);
    $currentValue = $this->__get('value');

    foreach ($children_keys as $childIndex) {
      $this->listItems[] = (object)[
        'value'    => ucfirst($childIndex),
        'text'     => str_repeat('&emsp;', $level - 1) . '|&mdash; ' . $titles[$childIndex],
        'disable'  => false,
        'class'    => '',
        'selected' => $childIndex == $currentValue ? true : false,
        'checked'  => $childIndex == $currentValue ? true : false,
        'onclick'  => '',
        'onchange' => ''
      ];

      $this->addChildren($index, $titles, $childIndex, $level + 1);
    }
  }
}
