<?php
/**
 * @package     ClawCorpLib
 * @subpackage  lib_claw
 *
 * @copyright   Copyright (C) 2022 C.L.A.W. Corp. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/** @var \Composer\Autoload\ClassLoader $autoLoader */
$autoLoader = include JPATH_LIBRARIES . '/vendor/autoload.php';

if ($autoLoader)
{
	$autoLoader->setPsr4('ClawCorpLib\\', __DIR__);
	define('CLAW_INCLUDED', 1);
}