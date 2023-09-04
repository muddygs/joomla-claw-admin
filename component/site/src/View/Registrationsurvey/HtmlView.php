<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Site\View\Registrationsurvey;

defined('_JEXEC') or die;

use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Lib\Aliases;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

/** @package ClawCorp\Component\Claw\Site\Controller */
class HtmlView extends BaseHtmlView
{
  public function display($tpl = null)
  {
    $this->state = $this->get('State');
    $this->form  = $this->get('Form');
    $this->item  = $this->get('Item');

    /** @var Joomla\CMS\Application\SiteApplication */
    $app = Factory::getApplication();
    $this->params = $params = $app->getParams();
    $eventAlias = $params->get('eventAlias', Aliases::current());
    Helpers::sessionSet('eventAlias', $eventAlias);
    
    parent::display($tpl ?? $eventAlias);
  }
}
