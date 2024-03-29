<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Site\View\Skillsubmission;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Lib\EventInfo;

/** @package ClawCorp\Component\Claw\View\Skillsubmission */
class HtmlView extends BaseHtmlView
{
  public function display($tpl = null)
  {
    // Check that user is in the submission group
    /** @var \Joomla\CMS\Application\SiteApplication */
    $app = Factory::getApplication();
    $groups = $app->getIdentity()->getAuthorisedGroups();
    $uid = $app->getIdentity()->id;

    $this->state = $this->get('State');
    $this->form  = $this->get('Form');
    $this->item  = $this->get('Item');

    // Validate ownership of the record
    if (property_exists($this->item, 'id')) {
      if ($this->item->id > 0) {
        if ($this->item->owner != $uid) {
          throw new GenericDataException('You do not have permission to edit this record.', 403);
        }
      }
    }

    // Put the record id in the session
    Helpers::sessionSet('recordid', $this->item->id ?? 0);

    // Check for errors.
    $errors = $this->get('Errors');
    if ($errors != null && count($errors)) {
      throw new GenericDataException(implode("\n", $errors), 500);
    }

    $params = $this->params = $this->state->get('params');
    $temp = clone ($params);

    // Check that user is in the submission group
    /** @var \Joomla\CMS\Application\SiteApplication */
    $app = Factory::getApplication();
    $groups = $app->getIdentity()->getAuthorisedGroups();

    $controllerMenuId = (int)Helpers::sessionGet('menuid');
    $menu = $app->getMenu()->getActive();
    if ($controllerMenuId != $menu->id) {
      $sitemenu = $app->getMenu();
      $sitemenu->setActive($controllerMenuId);
      $menu = $app->getMenu()->getActive();
    }
    $paramsMenu = $menu->getParams();
    $temp->merge($paramsMenu);

    $this->params = $temp;

    if ($this->params->get('se_group', 0) == 0 || !in_array($this->params->get('se_group'), $groups)) {
      $app->enqueueMessage('You do not have permission to access this resource. Please sign in.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);

      // Redirect to login
      $return = \Joomla\CMS\Uri\Uri::getInstance()->toString();
      $url    = 'index.php?option=com_users&view=login';
      $url   .= '&return='.base64_encode($return);
      $app->redirect($url);  
    }

    // Read-only is handled in the template by omitting the form and submit button
    
    // Event Naming
    $this->eventInfo = new EventInfo(Aliases::current(true));

    parent::display($tpl);
  }
}
