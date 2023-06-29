<?php
/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2022 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\View\Coupongenerator;

defined('_JEXEC') or die;

use ClawCorpLib\Helpers\Helpers;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

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
		$model       = $this->getModel();
		$this->form  = $this->get('Form');
		$this->avl   = Helpers::getUserViewLevelsByName(Factory::getContainer()->get('DatabaseDriver'));

		// // Check for errors.
		// if (count($errors = $this->get('Errors'))) {
		// 	throw new GenericDataException(implode("\n", $errors), 500);
		// }

		// $this->addToolbar();

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
	// protected function addToolbar()
	// {
	// 	Factory::getApplication()->input->set('hidemainmenu', true);
	// 	$isNew      = ($this->item->id == 0);

	// 	// $canDo = ContentHelper::getActions('com_countrybase');

	// 	$toolbar = Toolbar::getInstance();

	// 	ToolbarHelper::title(
	// 		'CLAW Sponsor ' . ($isNew ? 'Add' : 'Edit')
	// 	);

	// 	if (true /*$canDo->get('core.create')*/)
	// 	{
	// 		if ($isNew)
	// 		{
	// 			$toolbar->apply('sponsor.save');
	// 		}
	// 		else
	// 		{
	// 			$toolbar->apply('sponsor.apply');
	// 		}
	// 		$toolbar->save('sponsor.save');

	// 	}
	// 	$toolbar->cancel('sponsor.cancel', 'JTOOLBAR_CLOSE');
	// }

}