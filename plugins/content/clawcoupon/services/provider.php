<?php

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Clawcorp\Plugin\Content\Clawcoupon\Extension\Clawcoupon;

return new class() implements ServiceProviderInterface
{
  public function register(Container $container)
  {
    $container->set(
      PluginInterface::class,
      function (Container $container) {
        $config = (array) PluginHelper::getPlugin('content', 'clawcoupon');
        $subject = $container->get(DispatcherInterface::class);
        $app = Factory::getApplication();

        $plugin = new Clawcoupon($subject, $config);
        $plugin->setApplication($app);

        return $plugin;
      }
    );
  }
};
