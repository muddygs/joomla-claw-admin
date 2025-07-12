<?php

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Clawcorp\Plugin\Content\Clawreg\Extension\Clawreg;

return new class() implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container->set(
            PluginInterface::class,
            function (Container $container) {

                $config = (array) PluginHelper::getPlugin('content', 'clawreg');
                $subject = $container->get(DispatcherInterface::class);
                $app = Factory::getApplication();
                
                $plugin = new Clawreg($subject, $config);
                $plugin->setApplication($app);

                return $plugin;
            }
        );
    }
}
