<?php

namespace ClawCorp\Component\Claw\Administrator\Field;

use Joomla\CMS\Form\Field\TextField;

defined('_JEXEC') or die;

class HtmxTextField extends TextField
{
  use HtmxFieldTrait;

  protected $type = 'HtmxText';
  protected $layout = 'htmxtext';
}
