<?php
if (!array_key_exists('DOCUMENT_ROOT', $_SERVER) || empty($_SERVER['DOCUMENT_ROOT'])) die();

define('_JEXEC', 1);
define('JPATH_BASE', $_SERVER['DOCUMENT_ROOT'] . '/');

require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';

$container = \Joomla\CMS\Factory::getContainer();
$container->alias('session.web', 'session.web.site')
  ->alias('session', 'session.web.site')
  ->alias('JSession', 'session.web.site')
  ->alias(\Joomla\CMS\Session\Session::class, 'session.web.site')
  ->alias(\Joomla\Session\Session::class, 'session.web.site')
  ->alias(\Joomla\Session\SessionInterface::class, 'session.web.site');

// Instantiate the application.
/** @var \Joomla\CMS\Application\AdministratorApplication */
$app = $container->get(\Joomla\CMS\Application\AdministratorApplication::class);

// Set the application as global app
\Joomla\CMS\Factory::$application = $app;

// Require the CLAW Libraries
// if (!defined('CLAW_INCLUDED') && !@include_once(JPATH_LIBRARIES . '/claw/init.php')) {
//   throw new RuntimeException('CLAW library is not installed', 500);
// }

/** @var \Composer\Autoload\ClassLoader $autoLoader */
$autoLoader = include JPATH_LIBRARIES . '/vendor/autoload.php';

if ($autoLoader)
{
	$autoLoader->setPsr4('ClawCorpLib\\', JPATH_LIBRARIES . '/claw');
	define('CLAW_INCLUDED', 1);
} else {
  die('Autoloader failed in cron task');
}

use ClawCorpLib\Lib\Authnetprofile;
use Joomla\CMS\Component\ComponentHelper;

$componentParams = ComponentHelper::getParams('com_claw');
$alias = $componentParams->get('profiles_eventalias', '');
$cronkey = $componentParams->get('profiles_cronkey', '');

$key = $app->input->getString('key', '');
$cron = $app->input->getString('cron', '');
$limit = $app->input->getUint('limit', 25);
$cron = $cron == '' ? true : false;

if ( $key != $cronkey ) exit;
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset=UTF-8>
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
  <meta http-equiv="Pragma" content="no-cache" />
  <meta http-equiv="Expires" content="0" />
  <title>PROFILES</title>
</head>

<body>
<?php

$count = Authnetprofile::create( eventAlias: $alias, maximum_records:$limit, cron:$cron );

echo '<h1>Profiles created: ' . $count . '</h1>';

?>
</body>