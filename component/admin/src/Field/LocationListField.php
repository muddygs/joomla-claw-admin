<?php

namespace ClawCorp\Component\Claw\Administrator\Field;

use Joomla\CMS\Form\Field\ListField;
use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Helpers\Locations;

defined('_JEXEC') or die;

class LocationListField extends ListField
{
  protected $type = "LocationList";

  private $listItems = [];

  public $filter = '';

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

    $currentValue = $this->__get('value');
    if ($currentValue === '') {
      $data['value'] = Locations::$blankLocation;
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
    $rootLocations = Locations::GetRootLocationIds();

    $currentValue = $this->__get('value');

    // Assume "Select message" is first item (based on form config)
    $selectCase = false;

    foreach ($this->element->xpath('option') as $option) {
      $value = (string) $option['value'];
      $text  = trim((string) $option) ?: $value;

      // TODO: Fix "Select Location" case
      if (in_array($value, $rootLocations) || !$selectCase ) {
        $this->listItems[] = (object)[
          'value'    => $value,
          'text'     => $text,
          'disable'  => true,
          'class'    => '',
          'selected' => $value == $currentValue,
          'checked'  => $value == $currentValue,
          'onclick'  => '',
          'onchange' => ''
        ];

        $selectCase = true;
      } else {
        $level=1;
        $this->listItems[] = (object)[
          'value'    => $value,
          'text'     => str_repeat('&emsp;', $level - 1) . '|&mdash; ' . $text,
          'disable'  => false,
          'class'    => '',
          'selected' => $value == $currentValue,
          'checked'  => $value == $currentValue,
          'onclick'  => '',
          'onchange' => ''
        ];
      }  
    }

    return $this->listItems;
  }

  // private function addChildren(&$index, &$titles, int $parent_id, int $level)
  // {
  //   // Are there children? If not, return option
  //   $children_keys = array_keys($index, $parent_id, true);
  //   $currentValue = $this->__get('value');

  //   foreach ($children_keys as $childIndex) {
  //     $this->listItems[] = (object)[
  //       'value'    => ucfirst($childIndex),
  //       'text'     => str_repeat('&emsp;', $level - 1) . '|&mdash; ' . $titles[$childIndex],
  //       'disable'  => false,
  //       'class'    => '',
  //       'selected' => $childIndex == $currentValue ? true : false,
  //       'checked'  => $childIndex == $currentValue ? true : false,
  //       'onclick'  => '',
  //       'onchange' => ''
  //     ];

  //     $this->addChildren($index, $titles, $childIndex, $level + 1);
  //   }
  // }
}
