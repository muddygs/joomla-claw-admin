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
use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Lib\EventInfo;
use ClawCorpLib\Enums\ConfigFieldNames;
use ClawCorpLib\Helpers\Config;
use ClawCorpLib\Helpers\DbBlob;

/** @package ClawCorp\Component\Claw\Site\Controller */
class HtmlView extends BaseHtmlView
{
  public bool $canEdit = false;
  public bool $canAddOnly = false;
  public ?EventInfo $eventInfo = null;

  public function __construct($config = array())
  {
    parent::__construct($config);

    $this->eventInfo = new EventInfo(Aliases::current(true));
  }

  public function display($tpl = null)
  {
    // Check that user is in the submission group
    /** @var \Joomla\CMS\Application\SiteApplication */
    $app = Factory::getApplication();
    $groups = $app->getIdentity()->getAuthorisedGroups();
    $uid = $app->getIdentity()->id;

    $this->state = $this->get('State');
    $this->form  = $this->get('Form');
    /** @var \Joomla\CMS\Object\CMSObject */
    $this->item  = $this->get('Item');

    // Validate ownership of the record
    if (property_exists($this->item, 'id')) {
      if ($this->item->id > 0) {
        if ($this->item->uid != $uid) {
          throw new GenericDataException('You do not have permission to edit this record.', 403);
        }
      }
    }

    // Check for errors.
    $errors = $this->get('Errors');
    if ($errors != null && count($errors)) {
      throw new GenericDataException(implode("\n", $errors), 500);
    }

    $params = $this->params = $this->state->get('params');
    $temp = clone ($params);

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
      $app->enqueueMessage('You do not have permission to access this resource.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
      $app->redirect('/');
    }

    $this->canEditBio = $this->params->get('se_submissions_open') != 0;
    $this->canAddOnlyBio = $this->params->get('se_submissions_bioonly') != 0;

    // In read-only mode? New bios accepted, but current ones are locked

    if (!$this->canEditBio && $this->item->id > 0) {
      $fieldSet = $this->form->getFieldset('userinput');
      foreach ($fieldSet as $field) {
        $this->form->setFieldAttribute($field->getAttribute('name'), 'readonly', 'true');
      }
    }

    # used in controller for managing data update during save task
    $this->image_preview_path = $this->getModel()->getPresenterImagePath($this->item->id, $this->item->event);
    Helpers::sessionSet('image_preview', $this->image_preview_path);

    parent::display($tpl);
  }
}
