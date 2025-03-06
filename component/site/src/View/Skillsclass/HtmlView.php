<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Site\View\Skillsclass;

defined('_JEXEC') or die;

use ClawCorpLib\Enums\ConfigFieldNames;
use ClawCorpLib\Helpers\Config;
use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Helpers\Locations;
use ClawCorpLib\Skills\Presenters;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use ClawCorpLib\Skills\Skill;

/** @package ClawCorp\Component\Claw\Site\Controller */
class HtmlView extends BaseHtmlView
{
  /**
   * Execute and display a single class listing.
   *
   * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
   *
   * @return  void
   */
  public function display($tpl = null)
  {
    $this->state = $this->get('State');
    $input = Factory::getApplication()->getInput();

    /** @var \Joomla\CMS\Application\SiteApplication */
    $app = Factory::getApplication();

    $viewMenuId = Helpers::sessionGet('skillslist.menuid', 0);

    if (0 == $viewMenuId) {
      $app->enqueueMessage('Class listing must be reloaded. Reselect the menu item to continue.', 'info');
      $app->redirect(Route::_('/'));
    }

    $sitemenu = $app->getMenu();
    $sitemenu->setActive($viewMenuId);
    $menu = $app->getMenu()->getActive();

    $this->params = $menu->getParams();

    $cid = $input->get('id', 0);

    try {
      $this->class = new Skill($cid);
    } catch (\Exception) {
      $app->enqueueMessage('Class not found.', 'error');
      $app->redirect(Route::_('index.php?option=com_claw&view=skillslist'));
    }

    // Now load the presenters
    $this->presenters = Presenters::bySkill($this->class);

    $this->urlTab = $app->input->get('tab', 'overview', 'string');
    // Class detail should always come from class list
    $this->backLink = Route::_('index.php?option=com_claw&view=skillslist&tab=' . $this->urlTab);

    $config = new Config($this->class->event);
    $this->time_slots = $config->getConfigValuesText(ConfigFieldNames::SKILL_TIME_SLOT);
    $locations = Locations::get($this->class->event);
    $this->location = $locations[$this->class->location]->value ?? null;

    $this->category = $config->getConfigText(ConfigFieldNames::SKILL_CATEGORY, $this->class->category, '');

    parent::display($tpl);
  }
}
