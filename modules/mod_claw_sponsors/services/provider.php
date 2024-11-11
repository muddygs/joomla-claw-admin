<?php

/**
 * @package     ClawCorp.Module.Sponsors
 * @subpackage  mod_claw_sponsors
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
 * The tab ferret module service provider.
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
    $container->registerServiceProvider(new ModuleDispatcherFactory('\\ClawCorp\\Module\\Sponsors'));
    $container->registerServiceProvider(new HelperFactory('\\ClawCorp\\Module\\Sponsors\\Site\\Helper'));

    $container->registerServiceProvider(new Module());
  }
};
