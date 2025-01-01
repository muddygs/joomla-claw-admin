<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Site\View\Skillslist;

defined('_JEXEC') or die;

use ClawCorpLib\Enums\ConfigFieldNames;
use ClawCorpLib\Helpers\Config;
use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Helpers\Locations;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use ClawCorpLib\Lib\Aliases;
use Joomla\CMS\Router\Route;

/** @package ClawCorp\Component\Claw\Site\View\Skillslist\HtmlView */
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

    /** @var \Joomla\CMS\Menu\MenuItem */
    $menu = $app->getMenu()->getActive();
    $viewMenuId = Helpers::sessionGet('skillslist.menuid', 0);
    if ($viewMenuId != $menu->id && $viewMenuId != 0) {
      $sitemenu = $app->getMenu();
      $sitemenu->setActive($viewMenuId);
      $menu = $app->getMenu()->getActive();
    }

    if ($menu->link != 'index.php?option=com_claw&view=skillslist') {
      Helpers::sessionSet('skillslist.menuid', 0);
      $app->enqueueMessage('Class listing must be reloaded. Reselect the menu item to continue.', 'info');
      $app->redirect(Route::_('/'));
    }

    Helpers::sessionSet('skillslist.menuid', $menu->id);

    $this->params = $menu->getParams();
    $this->list_type = $this->params->get('list_type', 'simple');

    /** @var \ClawCorp\Component\Claw\Site\Model\SkillslistModel */
    $model = $this->getModel();
    $this->eventAlias = $this->params->get('event_alias', Aliases::current(true));

    $this->list = $model->GetConsolidatedList($this->eventAlias, $this->list_type);

    $this->locations = new Locations($this->eventAlias);

    $this->listType = $this->params->get('list_type') ?? 'simple';
    $this->urlTab = $app->input->get('tab', 'overview', 'string');

    $this->include_room = $this->params->get('include_room', 0);
    $this->enable_surveys = $this->params->get('enable_surveys', 0);

    if (!property_exists($this->list->tabs, $this->urlTab)) {
      $this->urlTab = 'overview';
    }

    $config = new Config($this->eventAlias);
    $this->time_slots = $config->getConfigValuesText(ConfigFieldNames::SKILL_TIME_SLOT);

    parent::display($tpl);
  }
}
