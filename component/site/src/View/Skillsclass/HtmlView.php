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

use ClawCorpLib\Helpers\Helpers;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use ClawCorpLib\Lib\Aliases;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

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

    /** @var \Joomla\CMS\Application\SiteApplication */
    $app = Factory::getApplication();

    $viewMenuId = (int)Helpers::sessionGet('skillslist.menuid');

    if ( 0 == $viewMenuId ) {
      $app->enqueueMessage('Class listing must be reloaded. Reselect the menu item to continue.', 'info');
      $app->redirect(Route::_('/'));
    }

    $sitemenu = $app->getMenu();
    $sitemenu->setActive($viewMenuId);
    $menu = $app->getMenu()->getActive();

    $this->params = $menu->getParams();
    
    $uri = Uri::getInstance();
    $params = $uri->getQuery(true);
    $cid = $params['id'] ?? 0;

    /** @var \ClawCorp\Component\Claw\Site\Model\SkillsclassModel */
    $model = $this->getModel();
    $this->class = $model->GetClass($cid, $this->params->get('event_alias', Aliases::current()));

    if ( is_null($this->class)) {
      $app->enqueueMessage('Class not found.', 'error');
      $app->redirect(Route::_('index.php?option=com_claw&view=skillslist'));
    }

    $this->urlTab = $app->input->get('tab', 'overview', 'string');
    // Class detail should always come from class list
    $this->backLink = Route::_('index.php?option=com_claw&view=skillslist&tab=' . $this->urlTab);

    parent::display($tpl);
  }
}
