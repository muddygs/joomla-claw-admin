<?php

/**
 * @package     ClawCorp.Module.Tabferret
 * @subpackage  mod_claw_tabferret
 *
 * @copyright   (C) 2024 C.L.A.W. Corp.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Module\ClawTabferret\Site\Dispatcher;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Dispatcher class for mod_claw_tabferret
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

    [$tabTitles, $tabData, $tabActive, $config] = $this->getHelperFactory()->getHelper('ClawTabferretHelper')->getTabData($data['params'], $data['app']);

    $data['tabs'] = $tabTitles;
    $data['tabContents'] = $tabData;
    $data['tabActive'] = $tabActive;
    $data['config'] = $config;

    return $data;
  }
}
