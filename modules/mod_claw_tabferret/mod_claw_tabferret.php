<?php

/**
 * @package     CLAW.Tabferret
 * @subpackage  mod_claw_tabferret
 *
 * @copyright   (C) 2024 C.L.A.W. Corp.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;

$tmpl = $params->get('type', 'default');
if ( $tmpl == 'tabs' ) $tmpl = 'default';

require ModuleHelper::getLayoutPath('mod_claw_tabferret', $tmpl);
