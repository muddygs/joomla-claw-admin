<?php

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use ClawCorpLib\Lib\Jwtwrapper;

Jwtwrapper::redirectOnInvalidToken('badge-checkin', $this->token);

/** @var Joomla\CMS\Application\SiteApplication */
$app = Factory::getApplication();
/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $app->getDocument()->getWebAssetManager();
$wa->useScript('com_claw.jwtmon');
$wa->useScript('com_claw.checkin');
$wa->useStyle('com_claw.admin');


?>
<div class="mb-2 p-1 bg-info text-white" id="jwtstatus"></div>

<h1>Badge Checkin Station</h1>

<?php
$this->setLayout('badge-search-form');
$this->page = 'badge-checkin';
echo $this->loadTemplate();
?>

<div id="status"></div>
