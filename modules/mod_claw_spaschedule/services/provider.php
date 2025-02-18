<?php

/**
 * @package     ClawCorp.Module.Spaschedule
 * @subpackage  mod_claw_spaschedule
 *
 * @copyright   (C) 2024 C.L.A.W. Corp.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Extension\Service\Provider\HelperFactory;
use Joomla\CMS\Extension\Service\Provider\Module;
use Joomla\CMS\Extension\Service\Provider\ModuleDispatcherFactory;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * The spa schedule module service provider.
 *
 * @since  4.4.0
 */
return new class() implements ServiceProviderInterface {
  /**
   * Registers the service provider with a DI container.
   *
   * @param   Container  $container  The DI container.
   *
   * @return  void
   *
   * @since   4.4.0
   */
  public function register(Container $container): void
  {
    $container->registerServiceProvider(new ModuleDispatcherFactory('\\ClawCorp\\Module\\Spaschedule'));
    $container->registerServiceProvider(new HelperFactory('\\ClawCorp\\Module\\Spaschedule\\Site\\Helper'));

    $container->registerServiceProvider(new Module());
  }
};
