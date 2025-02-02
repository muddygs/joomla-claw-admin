<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2025 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Site\View\Checkin;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;

class HtmxOverviewView extends BaseHtmlView
{
  function display($tpl = null)
  {
    $this->state = $this->get('State');

    /** @var \Joomla\CMS\Application\SiteApplication */
    $app = Factory::getApplication();
    /** @var \Joomla\CMS\Extension\MVCComponent */
    $c = $app->bootComponent('com_claw');
    $d = $c->getMVCFactory();

    /** @var \ClawCorp\Component\Claw\Administrator\Model\ReportsModel */
    $model = $d->createModel('Reports', 'Administrator');
    $this->items = $model->getVolunteerOverview();
    $this->setLayout('htmx_overview');

    parent::display($tpl);
  }
}
