<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2025 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Site\View\Checkin;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmxSearchView extends BaseHtmlView
{
  public string $search; // set in CheckinController

  function display($tpl = null)
  {
    /** @var \ClawCorp\Component\Claw\Site\Model\CheckinModel */
    $model = $this->getModel();
    $this->data = $model->search($this->search, $this->page);

    $this->setLayout('htmx_search_listing');
    parent::display($tpl);
  }
}
