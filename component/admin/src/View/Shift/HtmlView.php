<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\View\Shift;

defined('_JEXEC') or die;

use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Lib\ClawEvents;
use ClawCorpLib\Lib\EventInfo;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

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

    $eventAlias = $this->form->getField('event')->value ?? Aliases::current(true);

    try {
      $eventInfo = new EventInfo($eventAlias);
    } catch (\Exception) {
      Factory::getApplication()->enqueueMessage('Invalid event alias.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
      return false;
    }


    /** @var \Joomla\CMS\Form\Field\ListField */
    $parentField = $this->form->getField('shift_area');

    // TODO: replace shift_area column with category_id column (also Grids.php, ShiftsModel.php)
    $shiftCategoryIds = [...$eventInfo->eb_cat_shifts, ...$eventInfo->eb_cat_supershifts];
    $shiftRawCategories = ClawEvents::getRawCategories($shiftCategoryIds);

    foreach ($shiftRawCategories as $alias => $row) {
      // remove 'shifts-' prefix
      $k = substr($alias, 7);
      $parentField->addOption(htmlentities($row->name), ['value' => $k]);
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
   * @since   1.6
   */
  protected function addToolbar()
  {
    $app = Factory::getApplication();
    $app->input->set('hidemainmenu', true);
    $user = $app->getIdentity();

    $isNew      = ($this->item->id == 0);

    // $canDo = ContentHelper::getActions('com_claw');

    $toolbar = Toolbar::getInstance();

    ToolbarHelper::title(
      'Shift ' . ($isNew ? 'Add' : 'Edit')
    );

    if ($user->authorise('core.admin', 'com_claw')) {
      if ($isNew) {
        $toolbar->apply('shift.save');
      } else {
        $toolbar->apply('shift.apply');
      }
      $toolbar->save('shift.save');
    }
    $toolbar->cancel('shift.cancel', 'JTOOLBAR_CLOSE');
  }
}
