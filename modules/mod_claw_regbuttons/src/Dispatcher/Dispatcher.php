<?php

/**
 * @package     ClawCorp.Module.RegButtons
 * @subpackage  mod_claw_regbuttons
 *
 * @copyright   (C) 2024 C.L.A.W. Corp.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Module\RegButtons\Site\Dispatcher;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Dispatcher class for mod_claw_regbuttons
 */
class Dispatcher extends AbstractModuleDispatcher 
{
    /**
     * Returns the layout data.
     *
     * @return  array
     *
     * @since   4.4.0
     */
    protected function getLayoutData(): array
    {
        $data = parent::getLayoutData();

        $data['registration'] = $data['params']->get('registration', '');
        $data['schedule'] =  $data['params']->get('schedule', '');
        $data['skills'] = $data['params']->get('skills', '');
        $data['vendormart'] = $data['params']->get('vendormart', '');
        $data['silentauction'] = $data['params']->get('silentauction', '');
        $data['mobileapp'] = $data['params']->get('mobileapp', '');
        $data['hotels'] = $data['params']->get('hotels', '');
        $data['infotext'] = $data['params']->get('infotext', '');

        return $data;
    }
}
