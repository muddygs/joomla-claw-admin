<?php
/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\View\Presenter;

defined('_JEXEC') or die;

use ClawCorpLib\Enums\ConfigFieldNames;
use ClawCorpLib\Helpers\Config;
use ClawCorpLib\Helpers\DbBlob;
use ClawCorpLib\Lib\Aliases;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\User\User;

class HtmlView extends BaseHtmlView
{
  /**
   * The \JForm object
   *
   * @var  \JForm
   */
  protected $form;

  /**
   * The active item
   *
   * @var  object
   */
  protected $item;

  /**
   * The model state
   *
   * @var  object
   */
  protected $state;

  /**
   * The actions the user is authorised to perform
   *
   * @var  \JObject
   */
  protected $canDo;

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
    $this->state = $this->get('State');
    $this->form  = $this->get('Form');
    $this->item  = $this->get('Item');

    // Check for errors.
    if (count($errors = $this->get('Errors'))) {
      throw new GenericDataException(implode("\n", $errors), 500);
    }

    $config = new Config($this->item->event ?? Aliases::current(true));
    $path = $config->getConfigText(ConfigFieldNames::CONFIG_IMAGES, 'presenters') ?? '/images/skills/presenters/cache';

    // Make sure images are in the cache directory for display
    $cache = new DbBlob(
      db: $this->getModel('Presenter')->getDatabase(), 
      cacheDir: JPATH_ROOT . $path, 
      prefix: 'web_',
    );

    $filenames = $cache->toFile(
      tableName: '#__claw_presenters', 
      rowIds: [$this->item->id],
      key: 'image_preview',
    );

    $this->item->image_preview = $filenames[$this->item->id] ?? '';

    $cache = new DbBlob(
      db: $this->getModel('Presenter')->getDatabase(),
      cacheDir: JPATH_ROOT . $path, 
      prefix: 'orig_',
    );

    $filenames = $cache->toFile(
      tableName: '#__claw_presenters', 
      rowIds: [$this->item->id],
      key: 'image',
    );

    $this->item->image = $filenames[$this->item->id] ?? '';
    
    // If user is already defined, it cannot be changed
    $field = $this->form->getField('uid');
    $this->form->uid = 0;
    
    if ( $field->value ) {
      $this->form->uid = $field->value;
      $user = new User($this->form->uid);
      $name = $user->name;
      
      $field = $this->form->getField('uid_readonly_name');
      $this->form->setFieldAttribute($field->getAttribute('name'), 'default', $name);
      $field = $this->form->getField('uid_readonly_uid');
      $this->form->setFieldAttribute($field->getAttribute('name'), 'default', $this->form->uid);
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

    ToolbarHelper::title(
      'Skills Presenter ' . ($isNew ? 'Add' : 'Edit')
    );

    // If not checked out, can save the item.
    if ( $user->authorise('claw.skills', 'com_claw') ) {
        ToolbarHelper::apply('presenter.apply');
        ToolbarHelper::save('presenter.save');

        // If the form event is not current, allow copying to current
        if ( $this->item->event != Aliases::current() ) {
          ToolbarHelper::save2copy('presenter.save2copy', 'Copy to current');
        }
    }

    if ($isNew) {
      ToolbarHelper::cancel('presenter.cancel');
    } else {
      ToolbarHelper::cancel('presenter.cancel', 'JTOOLBAR_CLOSE');
    }

    ToolbarHelper::divider();
  }

}
