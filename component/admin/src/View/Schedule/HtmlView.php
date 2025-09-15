<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\View\Schedule;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
  /**
   * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
   * @return  void
   */
  function display($tpl = null)
  {
    /** @var \ClawCorp\Component\Claw\Administrator\Model\ScheduleModel */
    $model = $this->getModel();
    $this->form  = $model->getForm();
    $this->item  = $model->getItem();
    $this->state = $model->getState();

    // Check for errors.
    if (count($errors = $model->getErrors())) {
      throw new GenericDataException(implode("\n", $errors), 500);
    }

    $this->addToolbar();

    parent::display($tpl);
  }

  /**
   * Add the page title and toolbar.
   *
   * @return  void
   */
  protected function addToolbar()
  {
    $app = Factory::getApplication();
    $app->input->set('hidemainmenu', true);
    $user = $app->getIdentity();

    $isNew      = ($this->item->id == 0);

    ToolbarHelper::title(
      'CLAW Schedule ' . ($isNew ? 'Add' : 'Edit'),
      'calendar'
    );

    $toolbarButtons = [];

    if ($user->authorise('claw.events', 'com_claw')) {
      ToolbarHelper::apply('schedule.apply');
      $toolbarButtons[] = ['save', 'schedule.save'];

      $toolbarButtons[] = ['save2new', 'schedule.save2new'];

      if (!$isNew) {
        $toolbarButtons[] = ['save2copy', 'schedule.save2copy'];
      }

      ToolbarHelper::saveGroup(
        $toolbarButtons,
        'btn-success'
      );
    }

    if ($isNew) {
      ToolbarHelper::cancel('schedule.cancel');
    } else {
      ToolbarHelper::cancel('schedule.cancel', 'JTOOLBAR_CLOSE');
    }

    ToolbarHelper::divider();
  }
}
