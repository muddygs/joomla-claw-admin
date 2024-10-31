<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\Field;

use ClawCorpLib\Helpers\Config;
use ClawCorpLib\Helpers\Helpers;
use Joomla\CMS\Form\Field\ListField;
use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Enums\ConfigFieldNames;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Supports listing of class types for com_claw, view=skills
 */
class SkillsTrackListField extends ListField
{
  public $type = 'SkillsTrackList';

  protected function getOptions()
  {
    $options = parent::getOptions();
    $value = $this->__get('value');

    // Set by SkillsModel.php
    $eventAlias = Helpers::sessionGet('eventAlias', Aliases::current(true));
    $config = new Config($eventAlias);

    $types = $config->getConfigValuesText(ConfigFieldNames::SKILL_TRACK);

    foreach ($types as $k => $v) {
      $tmp = [
        'value'    => $k,
        'text'     => $v,
        'disable'  => false,
        'class'    => '',
        'selected' => $k == $value,
        'checked'  => $k == $value,
        'onclick'  => '',
        'onchange' => ''
      ];

      $options[] = (object)$tmp;
    }

    // Because this is what ListField (parent) does; I do not know if necessary
    reset($options);

    return $options;
  }
}
