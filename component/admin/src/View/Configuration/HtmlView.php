<?php
/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\View\Configuration;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * @package     Joomla.Administrator
 * @subpackage  com_claw
 *
 * @copyright   Copyright (C) 2020 John Smith. All rights reserved.
 * @license     GNU General Public License version 3; see LICENSE
 */

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
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		Factory::getApplication()->input->set('hidemainmenu', true);
		$isNew      = ($this->item->id == 0);
		$user  = Factory::getApplication()->getIdentity();

		$toolbar = Toolbar::getInstance();
		ToolbarHelper::title(
			'CLAW Configuration ' . ($isNew ? 'Add' : 'Edit')
		);

		if (true /*$canDo->get('core.create')*/)
		{
			if ($isNew)
			{
				$toolbar->apply('configuration.save');
			}
			else
			{
				$toolbar->apply('configuration.apply');
			}
			$toolbar->save('configuration.save');

		}

		$toolbar->cancel('configuration.cancel', 'JTOOLBAR_CLOSE');

		if ($user->authorise('core.admin', 'com_claw') || $user->authorise('core.options', 'com_claw'))
		{
			$toolbar->preferences('com_claw');
		}

	}

}
