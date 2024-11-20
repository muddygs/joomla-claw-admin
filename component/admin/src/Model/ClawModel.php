<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\AdminModel;

/**
 * Methods to handle a list of records.
 */
class ClawModel extends AdminModel
{
  protected $text_prefix = 'COM_CLAW';

  public function getForm($data = array(), $loadData = true)
  {
    $form = $this->loadForm('com_claw.claw', 'claw', array('control' => 'jform', 'load_data' => false));

    if (empty($form)) {
      return false;
    }

    return $form;
  }
}
