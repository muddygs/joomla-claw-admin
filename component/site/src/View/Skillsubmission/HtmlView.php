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

/** @package ClawCorp\Component\Claw\Site\Controller */
class HtmlView extends BaseHtmlView
{
  public function display($tpl = null)
  {
    $this->state = $this->get('State');
    $this->form  = $this->get('Form');
    $this->item  = $this->get('Item');
    
    // Check for errors.
    $errors = $this->get('Errors');
    if ($errors != null && count($errors)) {
      throw new GenericDataException(implode("\n", $errors), 500);
    }

    $params = $this->params = $this->state->get('params');
    $temp = clone($params);

    // Check that user is in the submission group
    /** @var \Joomla\CMS\Application\SiteApplication */
    $app = Factory::getApplication();
    $groups= $app->getIdentity()->getAuthorisedGroups();

    $controllerMenuId = (int)Helpers::sessionGet('menuid');
    $menu = $app->getMenu()->getActive();
    if ( $controllerMenuId != $menu->id ) {
      $sitemenu = $app->getMenu();
      $sitemenu->setActive($controllerMenuId);
      $menu = $app->getMenu()->getActive();
    }
    $paramsMenu = $menu->getParams();
    $temp->merge($paramsMenu);

    $this->params = $temp;

    if ( $this->params->get('se_group',0) == 0 || !in_array( $this->params->get('se_group'), $groups ))
    {
      $app->enqueueMessage('You do not have permission to access this resource.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
      $app->redirect('/');  
    }

    // In read-only mode?
    if ( $this->params->get('se_submissions_open') == 0) {
      $fieldSet = $this->form->getFieldset('userinput');
      foreach ( $fieldSet AS $field) {
        $this->form->setFieldAttribute($field->getAttribute('name'), 'readonly', 'true');
      }
    }

    parent::display($tpl);
  }
}
