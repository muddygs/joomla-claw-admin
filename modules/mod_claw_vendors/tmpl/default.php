<?php

/**
 * @package     ClawCorp.Site
 * @subpackage  mod_claw_vendors
 *
 * @copyright   (C) 2023 C.L.A.W. Corp.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use ClawCorp\Module\Vendors\Site\Helper\VendorsHelper;
use ClawCorpLib\Lib\Aliases;

$event = $params->get('event', Aliases::current());
VendorsHelper::echoVendors($event);
