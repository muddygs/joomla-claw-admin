<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\View\Claw;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
  function display($tpl = null)
  {
    /** @var \Joomla\CMS\MVC\Model\AdminModel */
    $model = $this->getModel();
    $this->form = $model->getForm();
    parent::display($tpl);
  }
}
