<?php

/**
 * @package     ClawCorp.Module.Sponsors
 * @subpackage  mod_claw_sponsors
 *
 * @copyright   (C) 2024 C.L.A.W. Corp.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Module\Sponsors\Site\Dispatcher;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;

\defined('JPATH_PLATFORM') or die;

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
   */
  protected function getLayoutData(): array
  {
    $data = parent::getLayoutData();

    $data['sponsorsByType'] = $this->getHelperFactory()->getHelper('SponsorsHelper')->loadSponsors($data['params'], $data['app']);

    return $data;
  }
}
