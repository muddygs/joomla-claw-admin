<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\Field;

use Joomla\CMS\Form\Field\TextField;

defined('_JEXEC') or die;

class HtmxTextField extends TextField
{
  use HtmxFieldTrait;

  protected $type = 'HtmxText';
  protected $layout = 'htmxtext';
}
