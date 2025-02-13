<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2025 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;
\defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/** @var Joomla\CMS\Application\SiteApplication */
$app = Factory::getApplication();
/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $app->getDocument()->getWebAssetManager();
$wa->useScript('com_claw.jwtmon');
$wa->useScript('com_claw.checkin');
$wa->useStyle('com_claw.admin');

?>
<div class="mb-2 p-1 text-bg-info text-end" id="jwtstatus"></div>

<h1 class="text-center">Badge Checkin Station</h1>

<?php
$this->setLayout('badge-search-form');
$this->page = 'badge-checkin';
echo $this->loadTemplate();
?>

<div id="status"></div>
