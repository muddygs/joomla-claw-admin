<?php
defined('_JEXEC') or die;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\Registry\Registry;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\Event;

return new class implements ServiceProviderInterface
{
  public function register(Container $container): void
  {
    // 1 – register the standard MVC dispatcher for this component
    $container->registerServiceProvider(
      new ComponentDispatcherFactory(\ClawCorp\Component\Claw\Site\View\Registrationoptions\HtmlView::class)
    );

    // 2 – once the application is in the container, hook onBeforeCompileHead
    $container->extend(
      SiteApplication::class,
      function (SiteApplication $app, Container $c): SiteApplication {
        // only for our exact URL parameters
        $input = $app->input;
        if (
          $input->get('option',  '', 'cmd') === 'com_claw'
          && $input->get('view',    '', 'cmd') === 'registrationoptions'
          && $input->get('event',   '', 'cmd') === 'l1125'
          && $input->get('action',  0,  'int') === 1
        ) {
          $app->getDispatcher()->addListener(
            'onBeforeCompileHead',
            function (Event $e) use ($app, $c) {
              /** @var DatabaseInterface $db */
              $db    = $c->get(DatabaseInterface::class);
              $query = $db->getQuery(true)
                ->select($db->quoteName(['template', 'params']))
                ->from($db->quoteName('#__template_styles'))
                ->where($db->quoteName('id') . ' = 17');
              $db->setQuery($query);

              if ($row = $db->loadObject()) {
                $params = new Registry;
                $params->loadString($row->params);
                $app->setTemplate($row->template, $params);
              }
            }
          );
        }

        return $app;
      }
    );
  }
};
