<?php

namespace ClawCorp\Component\Claw\Administrator\Field;

use Joomla\CMS\Form\Field\ListField;

defined('_JEXEC') or die;

class HtmxListField extends ListField
{
  use HtmxFieldTrait;

  protected $type = 'HtmxList';
  protected $layout = 'htmxlist';
}
