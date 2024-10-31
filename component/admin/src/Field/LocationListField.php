<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\Field;

use ClawCorpLib\Helpers\Helpers;
use Joomla\CMS\Form\Field\ListField;
use ClawCorpLib\Helpers\Locations;

defined('_JEXEC') or die;

class LocationListField extends ListField
{
  protected $type = "LocationList";

  /**
   * Method to get the field input markup for a generic list.
   * Use the multiple attribute to enable multiselect.
   *
   * @return  string  The field input markup.
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
    $options = parent::getOptions();

    $eventAlias = Helpers::sessionGet('eventAlias');
    $locations = new Locations($eventAlias);

    $value = $this->__get('value');

    foreach ($locations->GetLocationsList() as $location) {
      $tmp = (object)[
        'value'    => $location->id,
        'text'     => $location->value,
        'disable'  => false,
        'class'    => '',
        'selected' => $value == $value,
        'checked'  => $value == $value,
        'onclick'  => '',
        'onchange' => ''
      ];

      $options[] = $tmp;
    }

    reset($options);
    return $options;
  }
}
