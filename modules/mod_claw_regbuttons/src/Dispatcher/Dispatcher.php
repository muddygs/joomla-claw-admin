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
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Dispatcher class for mod_claw_regbuttons
 */
class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

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

        // $registration = $params->get('registration', '');
        // $schedule = $params->get('schedule', '');
        // $skills = $params->get('skills', '');
        // $vendormart = $params->get('vendormart', '');
        // $silentauction = $params->get('silentauction', '');
        // $mobileapp = $params->get('mobileapp', '');
        // $hotels = $params->get('hotels', '');
        // $infotext = $params->get('infotext', '');



        return $data;
    }
}
