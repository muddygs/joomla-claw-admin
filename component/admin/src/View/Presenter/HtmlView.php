<?php
/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2022 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\View\Presenter;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\User\User;

/**
 * @package     Joomla.Administrator
 * @subpackage  com_claw
 *
 * @copyright   Copyright (C) 2020 John Smith. All rights reserved.
 * @license     GNU General Public License version 3; see LICENSE
 */

/**
 * Main "Hello World" Admin View
 */
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

    /** @var Joomla\CMS\Application\AdministratorApplication */
    $app = Factory::getApplication();

    // Check for errors.
    if (count($errors = $this->get('Errors'))) {
      throw new GenericDataException(implode("\n", $errors), 500);
    }

    $this->addToolbar();

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
    Factory::getApplication()->input->set('hidemainmenu', true);
    $isNew      = ($this->item->id == 0);

    ToolbarHelper::title(
      'Skills Presenter ' . ($isNew ? 'Add' : 'Edit')
    );

    $toolbarButtons = [];

    // If not checked out, can save the item.
    if (true /*!$checkedOut && ($canDo->get('core.edit') || \count($user->getAuthorisedCategories('com_claw', 'core.create')) > 0)*/) {
        ToolbarHelper::apply('presenter.apply');
        $toolbarButtons[] = ['save', 'presenter.save'];

        if (true /*$canDo->get('core.create')*/) {
            $toolbarButtons[] = ['save2new', 'presenter.save2new'];
        }
    }

    // If an existing item, can save to a copy.
    if (!$isNew /*&& $canDo->get('core.create')*/) {
        $toolbarButtons[] = ['save2copy', 'presenter.save2copy'];
    }

    ToolbarHelper::saveGroup(
        $toolbarButtons,
        'btn-success'
    );


    if ($isNew) {
      ToolbarHelper::cancel('presenter.cancel');
    } else {
      ToolbarHelper::cancel('presenter.cancel', 'JTOOLBAR_CLOSE');
    }

    ToolbarHelper::divider();
  }

}
