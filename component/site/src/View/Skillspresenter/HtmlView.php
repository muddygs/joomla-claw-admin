<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Site\View\Skillspresenter;

defined('_JEXEC') or die;

use ClawCorpLib\Helpers\Helpers;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use ClawCorpLib\Lib\Aliases;
use Joomla\CMS\Uri\Uri;

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

    $controllerMenuId = (int)Helpers::sessionGet('skillsmenuid');
    $menu = $app->getMenu()->getActive();
    if ($controllerMenuId != $menu->id) {
      $sitemenu = $app->getMenu();
      $sitemenu->setActive($controllerMenuId);
      $menu = $app->getMenu()->getActive();
    }
    $this->params = $menu->getParams();

    
    $uri = Uri::getInstance();
    $params = $uri->getQuery(true);
    $uid = $params['id'] ?? 0;

    /** @var \ClawCorp\Component\Claw\Site\Model\SkillslistModel */
    $model = $this->getModel();
    $this->presenter = $model->GetPresenter($uid, $this->params->get('event_alias', Aliases::current()));
    parent::display($tpl);
  }
}
