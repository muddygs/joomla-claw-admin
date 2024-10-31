<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\Field;

use ClawCorpLib\Enums\EventPackageTypes;
use Joomla\CMS\Form\Field\ListField;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Supports an HTML list of CLAW events; based on com_menus@4.2.7
 *
 * @since  3.8.0
 */
class ClawEventPackageTypeListField extends ListField
{
  /**
   * The form field type.
   *
   * @var    string
   */
  protected $type = 'ClawEventPackageTypeList';

  /**
   * Method to get the field options.
   *
   * @return  array  The field option objects.
   */
  protected function getOptions()
  {
    $options = parent::getOptions();

    $currentValue = $this->__get('value') ?? '';

    foreach (EventPackageTypes::toOptions() as $key => $text) {
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
