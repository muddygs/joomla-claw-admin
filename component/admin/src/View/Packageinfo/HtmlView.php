<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\View\Packageinfo;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
  /**
   * The search tools form
   *
   * @var    Form
   * @since  1.6
   */
  public $filterForm;

  /**
   * The active search filters
   *
   * @var    array
   * @since  1.6
   */
  public $activeFilters = [];

  /**
   * Category data
   *
   * @var    array
   * @since  1.6
   */
  protected $categories = [];

  /**
   * An array of items
   *
   * @var    array
   * @since  1.6
   */
  protected $items = [];

  /**
   * The pagination object
   *
   * @var    Pagination
   * @since  1.6
   */
  protected $pagination;

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
   * @since   1.6
   */
  protected function addToolbar()
  {
    $app = Factory::getApplication();
    $app->input->set('hidemainmenu', true);
    $user = $app->getIdentity();

    $isNew      = ($this->item->id == 0);

    $toolbar = Toolbar::getInstance();

    ToolbarHelper::title(
      'CLAW Package Info ' . ($isNew ? 'Add' : 'Edit')
    );

    if ($user->authorise('admin.core', 'com_claw')) {
      if ($isNew) {
        $toolbar->apply('packageinfo.save');
      } else {
        $toolbar->apply('packageinfo.apply');
      }

      $toolbar->save('packageinfo.save');

      if ( !$isNew ) $toolbar->save2copy('packageinfo.save2copy', 'Make Copy');
    }

    $toolbar->cancel('packageinfo.cancel', 'JTOOLBAR_CLOSE');
  }
}