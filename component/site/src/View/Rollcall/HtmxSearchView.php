<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2025 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Site\View\Rollcall;

use ClawCorpLib\Helpers\Helpers;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmxSearchView extends BaseHtmlView
{
  public string $regid; // set in CheckinController

  function display($tpl = null)
  {
    /** @var \ClawCorp\Component\Claw\Site\Model\CheckinModel */
    $model = $this->getModel();
    $data = $model->volunteerSearch($this->regid);

    if (!$data['valid']) {
      echo '<strong>' . $data['message'] . '</strong>';
      return;
    }

    Helpers::sessionSet('rollcallRegid', $this->regid);

    $this->records = $data['shifts'];
    $this->setLayout('htmx_volunteerstatus');
    parent::display();
  }
}
