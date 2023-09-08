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
    if ($currentValue === '' || $currentValue === 0) {
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
          'disable'  => false,
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

  public function populateOptions(string $parentAlias = '', bool $rootOnly = false)
  {
    $locations = Locations::GetLocationsList($parentAlias, $rootOnly);

    if ( $parentAlias != '' ) {
      $this->addOption(htmlentities('TBD'), ['value' => Locations::$blankLocation]);
    }

    foreach ( $locations AS $l) {
      $this->addOption(htmlentities($l->value), ['value' => $l->id]);
    }
  }
}
