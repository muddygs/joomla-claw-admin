<?php

namespace ClawCorp\Component\Claw\Administrator\Field;

use Joomla\CMS\Form\Field\ListField;
use ClawCorpLib\Helpers\Helpers;

defined('_JEXEC') or die;

class LocationListField extends ListField
{
  protected $type = "LocationList";

  private $listItems = [];

  /**
   * Customized method to populate the field option groups.
   *
   * @return  array  The field option objects as a nested array in groups.
   */
  protected function getOptions()
  {
    $this->listItems = parent::getOptions();

    $locations = Helpers::getLocations($this->getDatabase());
    $index = array_column($locations, 'catid', 'id');
    $titles = array_column($locations, 'value', 'id');

    foreach ($locations as $l) {
      if ($l->catid == 0) {
        $this->listItems[] = (object)[
          'value'    => $l->id,
          'text'     => $titles[$l->id],
          'disable'  => false,
          'class'    => '',
          'selected' => false,
          'checked'  => false,
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

    foreach ($children_keys as $childIndex) {
      $this->listItems[] = (object)[
        'value'    => ucfirst($childIndex),
        'text'     => str_repeat('&emsp;', $level - 1). '|&mdash; '.$titles[$childIndex],
        'disable'  => false,
        'class'    => '',
        'selected' => false,
        'checked'  => false,
        'onclick'  => '',
        'onchange' => ''
      ];

      $this->addChildren($index, $titles, $childIndex, $level + 1);
    }
  }
}
