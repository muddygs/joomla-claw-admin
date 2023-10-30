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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/** @package ClawCorp\Component\Claw\Site\Controller */
class HtmlView extends BaseHtmlView
{
  /**
   * Execute and display a single presenter listing.
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
    $sitemenu = $app->getMenu();
    $sitemenu->setActive($viewMenuId);
    $menu = $app->getMenu()->getActive();

    $this->params = $menu->getParams();
    
    $uri = Uri::getInstance();
    $params = $uri->getQuery(true);
    $this->pid = $params['id'] ?? 0;
    $this->cid = $params['cid'] ?? 0;
    $this->urlTab = $params['tab'] ?? 'overview';

    /** @var \ClawCorp\Component\Claw\Site\Model\SkillslistModel */
    $model = $this->getModel();
    $this->presenter = $model->GetPresenter($this->pid, $this->params->get('event_alias', Aliases::current()));

    if ( is_null($this->presenter)) {
      $app->enqueueMessage('Presenter not found.', 'error');
      $app->redirect(Route::_('index.php?option=com_claw&view=skillslist'));
    }

    if ( $this->cid ) {
      // Route back to class
      $this->backLink = Route::_('index.php?option=com_claw&view=skillsclass&id=' . $this->cid) . '&tab=' . $this->urlTab;
    } else {
      // Route back to list
      $this->backLink = Route::_('index.php?option=com_claw&view=skillslist&tab=' . $this->urlTab);
    }
    
    parent::display($tpl);
  }
}
