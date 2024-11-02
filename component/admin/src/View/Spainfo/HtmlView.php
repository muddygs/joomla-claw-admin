<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\View\Spainfo;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\ToolbarHelper;
use ClawCorpLib\Lib\Aliases;

class HtmlView extends BaseHtmlView
{
  /**
   * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
   * @return  void
   */
  function display($tpl = null)
  {
    $this->form  = $this->get('Form');
    $this->item  = $this->get('Item');
    $this->state = $this->get('State');

    // Check for errors.
    if (count($errors = $this->get('Errors'))) {
      throw new GenericDataException(implode("\n", $errors), 500);
    }

    $this->addToolbar();

    parent::display($tpl);
  }

  /**
   * Add the page title and toolbar.
   *
   * @return  void
   *
   * @throws \Exception
   */
  protected function addToolbar()
  {
    $app = Factory::getApplication();
    $app->input->set('hidemainmenu', true);
    $user = $app->getIdentity();

    $isNew      = ($this->item->id == 0);

    ToolbarHelper::title(
      'CLAW Spa Event ' . ($isNew ? 'Add' : 'Edit')
    );

    // If not checked out, can save the item.
    if ($user->authorise('admin.core', 'com_claw')) {
      ToolbarHelper::apply('spainfo.apply');
      ToolbarHelper::save('spainfo.save');

      // If the form event is not current, allow copying to current
      if ($this->item->eventAlias == Aliases::current() && $this->item->id) {
        ToolbarHelper::save2copy('spainfo.save2copy', 'Save a copy');
      }
    }

    if ($isNew) {
      ToolbarHelper::cancel('spainfo.cancel');
    } else {
      ToolbarHelper::cancel('spainfo.cancel', 'JTOOLBAR_CLOSE');
    }
  }
}
