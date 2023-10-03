<?php
/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\View\Eventcopy;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;

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

		$user = Factory::getApplication()->getIdentity();

		$user = Factory::getApplication()->getIdentity();

		if ( $user->authorise('core.admin', 'com_claw') ) {
		} else {
			Factory::getApplication()->enqueueMessage('You do not have permission to access this page.', 'error');
			Factory::getApplication()->redirect('/administrator/index.php');
		}

		// Get the toolbar object instance
		$toolbar = Toolbar::getInstance('toolbar');

		$toolbar->standardButton('refresh')
		->text('Rebuild Mappings')
		->task('eventcopy.repair')
		->formValidation(false);
		
		parent::display($tpl);
	}
}
