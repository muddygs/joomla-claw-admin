<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\View\Sponsors;

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
   * The model state
   *
   * @var    Registry
   * @since  1.6
   */
  protected $state;

  /**
   * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
   * @return  void
   */
  function display($tpl = null)
  {
    $this->state      = $this->get('State');
    $this->items      = $this->get('Items');
    $this->pagination = $this->get('Pagination');
    $this->filterForm    = $this->get('FilterForm');
    $this->activeFilters = $this->get('ActiveFilters');

    // Check for errors.
    if (count($errors = $this->get('Errors'))) {
      throw new GenericDataException(implode("\n", $errors), 500);
    }

    $this->addToolbar();

    parent::display($tpl);
  }

  protected function addToolbar(): void
  {
    $app = Factory::getApplication();

    ToolbarHelper::title('CLAW Sponsors');

    // Get the toolbar object instance
    $toolbar = Toolbar::getInstance('toolbar');

    $user  = $app->getIdentity();

    if ($user->authorise('claw.events', 'com_claw')) {
      $toolbar->addNew('sponsor.add');
      $toolbar->delete('sponsors.delete')
        ->text('Deleted Selected')
        ->message('Confirm delete selected sponsor(s)?')
        ->listCheck(true);
      $toolbar->basicButton('eblast', 'Eblast Graphics', 'sponsors.eblast')
        ->icon('fas fa-tools')
        ->buttonClass('btn')
        ->listCheck(false);
    }
  }
}
