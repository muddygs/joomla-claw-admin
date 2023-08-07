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
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use ClawCorpLib\Lib\Aliases;
use RegularLabs\Library\Alias;

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

    $controllerMenuId = (int)Helpers::sessionGet('menuid');
    Helpers::sessionSet('skillsmenuid', $controllerMenuId);
    $menu = $app->getMenu()->getActive();
    if ($controllerMenuId != $menu->id) {
      $sitemenu = $app->getMenu();
      $sitemenu->setActive($controllerMenuId);
      $menu = $app->getMenu()->getActive();
    }
    $this->params = $menu->getParams();
 
    $this->list_type = $this->params->get('list_type', 'simple');


    /** @var \ClawCorp\Component\Claw\Site\Model\SkillslistModel */
    $model = $this->getModel();
    $this->eventInfo = $model->GetEventInfo($this->params->get('event_alias', Aliases::current));

    $this->list = $model->GetConsolidatedList();

    parent::display($tpl);
  }
}
