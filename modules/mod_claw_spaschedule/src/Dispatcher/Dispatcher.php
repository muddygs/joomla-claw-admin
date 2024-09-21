<?php

/**
 * @package     ClawCorp.Module.Schedule
 * @subpackage  mod_claw_schedule
 *
 * @copyright   (C) 2024 C.L.A.W. Corp.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Module\Spaschedule\Site\Dispatcher;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Dispatcher class for mod_claw_schedule
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

    $data['eventConfig'] = $this->getHelperFactory()->getHelper('SpascheduleHelper')->loadSchedule($data['params'], $data['app']);

    return $data;
  }
}
