<?php

/**
 * @package     CLAW.Sponsors
 * @subpackage  plg_task_clawcorp
 *
 * @copyright   (C) 2024 C.L.A.W. Corp.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Plugin\Task\ClawCorp\Extension\Tasks;

return new class() implements ServiceProviderInterface {
  /**
   * Registers the service provider with a DI container.
   *
   * @param   Container  $container  The DI container.
   *
   * @return  void
   *
   * @since   4.2.0
   */
  public function register(Container $container)
  {
    $container->set(
      PluginInterface::class,
      function (Container $container) {
        $dispatcher = $container->get(DispatcherInterface::class);

        $plugin = new Tasks(
          $dispatcher,
          (array) PluginHelper::getPlugin('task', 'clawcorp')
        );
        $plugin->setApplication(Factory::getApplication());

        return $plugin;
      }
    );
  }
};
