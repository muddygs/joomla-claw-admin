<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Site\View\Skillslist;

defined('_JEXEC') or die;

use ClawCorpLib\Helpers\Helpers;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use ClawCorpLib\Lib\Aliases;

/** @package ClawCorp\Component\Claw\Site\Controller */
class HtmlView extends BaseHtmlView
{
  /**
   * Execute and display a template script.
   *
   * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
   *
   * @return  void
   */
  public function display($tpl = null)
  {
    $this->state = $this->get('State');

    /** @var Joomla\CMS\Application\SiteApplication */
    $app = Factory::getApplication();

    $viewMenuId = (int)Helpers::sessionGet('skillslist.menuid');
    $menu = $app->getMenu()->getActive();

    if ( 0 == $viewMenuId ) {
      $viewMenuId = $menu->id;
      Helpers::sessionSet('skillslist.menuid', $viewMenuId);
    }

    if ($viewMenuId != $menu->id) {
      $sitemenu = $app->getMenu();
      $sitemenu->setActive($viewMenuId);
      $menu = $app->getMenu()->getActive();
    }
    $this->params = $menu->getParams();
 
    $this->list_type = $this->params->get('list_type', 'simple');


    /** @var \ClawCorp\Component\Claw\Site\Model\SkillslistModel */
    $model = $this->getModel();
    $this->eventAlias = $this->params->get('event_alias', Aliases::current());

    $this->list = $model->GetConsolidatedList($this->eventAlias);
    
    $this->listType = $this->params->get('list_type') ?? 'simple';
    $this->urlTab = $app->input->get('tab', 'overview', 'string');

    if ( !property_exists($this->list->tabs, $this->urlTab) ) {
      $this->urlTab = 'overview';
    }

    parent::display($tpl);
  }
}
