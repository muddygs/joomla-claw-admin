<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2025 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\Field;

use Joomla\CMS\Form\Field\ListField;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Supports an HTML list of CLAW Locations (general)
 *
 * @since  3.8.0
 */
class ClawLocationsListField extends ListField
{
  /**
   * The form field type.
   *
   * @var    string
   */
  protected $type = 'ClawLocationsList';

  //TODO: Hard-coding for now
  private $locations = [
    0 => 'Select Location',
    1 => 'Cleveland',
    2 => 'Los Angeles',
    99 => 'Other',
  ];

  /**
   * Method to get the field input markup for a generic list.
   * Use the multiple attribute to enable multiselect.
   *
   * @return  string  The field input markup.
   */
  protected function getInput()
  {
    $data = $this->getLayoutData();
    $currentValue = $this->__get('value');

    if ($currentValue != '' && !array_key_exists($currentValue, $this->locations)) {
      $this->__set('value', $currentValue);
      $data['value'] = $currentValue;
    }

    $data['options'] = (array) $this->getOptions();

    return $this->getRenderer($this->layout)->render($data);
  }

  /**
   * Method to get the field options.
   *
   * @return  array  The field option objects.
   */
  protected function getOptions()
  {
    $options = parent::getOptions();

    $currentValue = $this->__get('value') ?? 0;

    foreach ($this->locations as $id => $location) {
      $options[] = (object)[
        'value'    => $id,
        'text'     => $location,
        'disable'  => false,
        'class'    => '',
        'selected' => $id == $currentValue,
        'checked'  => $id == $currentValue,
        'onclick'  => '',
        'onchange' => ''
      ];
    }

    // Because this is what ListField (parent) does; I do not know if necessary
    reset($options);

    return $options;
  }
}
