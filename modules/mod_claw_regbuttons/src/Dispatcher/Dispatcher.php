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

    $keys = [
      'registration',
      'schedule',
      'skills',
      'vendormart',
      'silentauction',
      'mobileapp',
      'hotels',
      'local',
      'infotext',
    ];

    foreach ($keys as $key) {
      $data[$key] = $data['params']->get($key, '');
    }

    return $data;
  }
}
