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

use ClawCorpLib\Helpers\Skills;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Toolbar\ToolbarFactoryInterface;

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

    $model = $this->getModel();

    foreach ( $this->items AS $item )
    {
      $classes = Skills::GetPresenterClasses($this->getModel()->db, $item->uid, 'c0423');

      $classesLink = array_map(function($class) {
        $url = Route::_("index.php?option=com_claw&view=skill&layout=edit&id={$class->id}");
        $link = '<a href="'.$url.'"> '.$class->title.'</a>';
        return $link;
      }, $classes);

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

    // TODO: This is the "new" way to do toolbars, but there are some formatting
    // issues that need to be resolved.


    // Get the toolbar object instance
    /** @var Toolbar $toolbar */
    $toolbar = Factory::getContainer()->get(ToolbarFactoryInterface::class)->createToolbar('toolbar');


    // TODO: Need both?
    if ($user->authorise('core.admin', 'com_claw') || $user->authorise('claw.skills', 'com_claw'))
    {
      $toolbar->addNew('presenter.add');

      $toolbar->delete('presenters.delete')
        ->text('Delete')
        ->message('Confirm delete selected presenter(s)?')
        ->listCheck(true);
    }

    return $toolbar;
  }
}
