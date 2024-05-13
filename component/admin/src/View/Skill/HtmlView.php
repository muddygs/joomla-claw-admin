<?php
/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\View\Skill;

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
		$this->state = $this->get('State');
		$this->form  = $this->get('Form');
		$this->item  = $this->get('Item');

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

		ToolbarHelper::title(
			'CLAW Skills Class ' . ($isNew ? 'Add' : 'Edit')
		);

		$user = Factory::getApplication()->getIdentity();

		if ( $user->authorise('claw.skills', 'com_claw') ) {
			ToolbarHelper::apply('skill.apply');
			ToolbarHelper::save('skill.save');
			ToolbarHelper::save('skill.save2copy','Copy Class');
		}

		if ($isNew) {
			ToolbarHelper::cancel('skill.cancel');
		} else {
			ToolbarHelper::cancel('skill.cancel', 'JTOOLBAR_CLOSE');
		}

		ToolbarHelper::divider();
	}

}
