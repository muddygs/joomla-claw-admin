<?php
/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2022 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

/**
 * Default Controller of Claw component
 */

class DisplayController extends BaseController {
    /**
     * The default view for the display method.
     *
     * @var string
     */
    protected $default_view = 'claw';

    public function __construct($config = [], MVCFactoryInterface $factory = null, $app = null, $input = null)
	{
		parent::__construct($config, $factory, $app, $input);
	}

    public function display($cachable = false, $urlparams = array()) {
        return parent::display($cachable, $urlparams);
    }
    
}
