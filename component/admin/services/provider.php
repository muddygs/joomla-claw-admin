<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\Extension\Service\Provider\CategoryFactory;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\Extension\Service\Provider\RouterFactory;

use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

return new class implements ServiceProviderInterface
{

  public function register(Container $container): void
  {
    $container->registerServiceProvider(new CategoryFactory('\\ClawCorp\\Component\\Claw'));
    $container->registerServiceProvider(new MVCFactory('\\ClawCorp\\Component\\Claw'));
    $container->registerServiceProvider(new ComponentDispatcherFactory('\\ClawCorp\\Component\\Claw'));
    $container->registerServiceProvider(new RouterFactory('\\ClawCorp\\Component\\Claw'));

    $container->set(
      ComponentInterface::class,
      function (Container $container) {
        $component = new MVCComponent($container->get(ComponentDispatcherFactoryInterface::class));
        $component->setMVCFactory($container->get(MVCFactoryInterface::class));

        return $component;
      }
    );
  }
};
