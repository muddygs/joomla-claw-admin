<?php

namespace ClawCorp\Component\Claw\Administrator\Field;

use Joomla\CMS\Form\Field\CheckboxesField;

defined('_JEXEC') or die;

class HtmxCheckBoxesField extends CheckboxesField
{
  use HtmxFieldTrait;

  protected $type = 'HtmxCheckBoxes';
  protected $layout = 'htmxcheckboxes';
}
