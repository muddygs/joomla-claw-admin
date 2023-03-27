<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Site\View\Presentersubmission;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

/** @package ClawCorp\Component\Claw\Site\Controller */
class HtmlView extends BaseHtmlView
{
  //  not using, but keeping as template to change layout in the future, if needed
  // edit.php and edit.xml are also needed, as appropriate, in this sample
  // function display($tpl = null)
  // {
  // 	$this->form  = $this->get('Form');
  // 	$this->item  = $this->get('Item');
  // 	$this->state = $this->get('State');

  // 	// Check for errors.
  // 	if (count($errors = $this->get('Errors'))) {
  // 		throw new GenericDataException(implode("\n", $errors), 500);
  // 	}

  // 	$this->setLayout('edit');

  // 	parent::display($tpl);
  // }

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

    $menu = $app->getMenu()->getActive();
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
