<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2025 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\Field;

use ClawCorpLib\Enums\EventSponsorshipTypes;
use Joomla\CMS\Form\Field\ListField;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Supports an HTML list of PackageInfoTypes.
 */
class EventSponsorshipTypeListField extends ListField
{
  /**
   * The form field type.
   *
   * @var    string
   * @since  1.7.0
   */
  protected $type = 'EventSponsorshipTypeList';

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
    $currentValue = is_array($currentValue) && count($currentValue) == 1 ? $currentValue[0] : 0;

    if ((int)$currentValue != 0) {
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

    $currentValue = $this->__get('value') ?? '';

    foreach (EventSponsorshipTypes::toOptions() as $key => $text) {
      $options[] = (object)[
        'value'    => $key,
        'text'     => $text,
        'disable'  => false,
        'class'    => '',
        'selected' => $key == $currentValue ? true : false,
        'checked'  => $key == $currentValue ? true : false,
        'onclick'  => '',
        'onchange' => ''
      ];
    }

    // Because this is what ListField (parent) does; I do not know if necessary
    reset($options);

    return $options;
  }
}
