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

/** @package ClawCorp\Component\Claw\Site\Controller */
class PrintcountView extends BaseHtmlView
{
  public function display($tpl = null)
  {
    $this->state = $this->get('State');
    /** @var \ClawCorp\Component\Claw\Site\Model\CheckinModel */
    $model = $this->getModel();
    $counts = $model->GetCount();

    $this->totalcount = $counts['all'];
    $this->attendeecount = $counts['attendee'];
    $this->volunteercount = $counts['volunteer'];
    $this->othercount = $counts['remainder'];

    $this->setLayout('htmx_badge_count');

    parent::display($tpl);
  }
}
