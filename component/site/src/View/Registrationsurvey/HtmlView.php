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

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use ClawCorpLib\Helpers\Helpers;

/** @package ClawCorp\Component\Claw\Site\Controller */
class HtmlView extends BaseHtmlView
{
  
  public function display($tpl = null)
  {
    $this->state = $this->get('State');
    $this->form  = $this->get('Form');
    $this->item  = $this->get('Item');
    
    // $params = $this->params = $this->state->get('params');
    // $temp = clone ($params);

    // // Check that user is in the submission group
    // /** @var \Joomla\CMS\Application\SiteApplication */
    // $app = Factory::getApplication();

    // $controllerMenuId = (int)Helpers::sessionGet('menuid');
    // $menu = $app->getMenu()->getActive();
    // if ($controllerMenuId != $menu->id) {
    //   $sitemenu = $app->getMenu();
    //   $sitemenu->setActive($controllerMenuId);
    //   $menu = $app->getMenu()->getActive();
    // }
    // $paramsMenu = $menu->getParams();
    // $temp->merge($paramsMenu);
    // $this->params = $temp;

    parent::display($tpl);
  }
}
