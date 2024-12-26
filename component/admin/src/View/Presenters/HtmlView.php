<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\View\Presenters;

defined('_JEXEC') or die;

use ClawCorpLib\Skills\Skills;
use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Lib\EventInfo;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
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

    $event = $this->state->get('filter.event', Aliases::current(true));
    $eventInfo = new EventInfo($event, true);

    foreach ($this->items as $item) {
      $skills = Skills::getByPresenterId($eventInfo, $item->id);

      $classesLink = array_map(function ($id) use ($skills) {
        $url = Route::_("index.php?option=com_claw&view=skill&layout=edit&id={$skills[$id]->id}");
        $link = '<a href="' . $url . '"> ' . $skills[$id]->title . '</a>';
        return $link;
      }, $skills->keys());

      $item->classes = implode('<br/>', $classesLink);
    }

    // Check for errors.
    if (count($errors = $this->get('Errors'))) {
      throw new GenericDataException(implode("\n", $errors), 500);
    }

    $this->toolbar = $this->addToolbar();

    parent::display($tpl);
  }

  protected function addToolbar(): Toolbar
  {
    $app = Factory::getApplication();
    $user  = $app->getIdentity();

    ToolbarHelper::title('CLAW Presenters');

    //$toolbar = Factory::getContainer()->get(ToolbarFactoryInterface::class)->createToolbar('toolbar');
    // TODO: When the backend is updated, we'll update.
    // See: https://manual.joomla.org/docs/general-concepts/dependency-injection/di-issues#toolbargetinstance

    /** @var \Joomla\CMS\Toolbar\Toolbar $toolbar */
    $toolbar = Toolbar::getInstance('toolbar');

    if ($user->authorise('core.admin', 'com_claw') || $user->authorise('claw.skills', 'com_claw')) {
      $toolbar->addNew('presenter.add');

      $toolbar->delete('presenters.delete')
        ->text('Delete')
        ->message('Confirm delete selected presenter(s)?')
        ->listCheck(true);
    }

    return $toolbar;
  }
}
