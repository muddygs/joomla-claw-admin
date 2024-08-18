<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */


namespace ClawCorp\Component\Claw\Administrator\Controller;

defined('_JEXEC') or die;

use ClawCorpLib\Helpers\Deploy;
use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Lib\EventInfo;
use ClawCorpLib\Traits\Controller;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Input\Input;

/**
 * Sponsorships list controller class.
 *
 * @since  1.6
 */
class SponsorshipsController extends AdminController
{
  use Controller;

  public function __construct(
    $config = [],
    MVCFactoryInterface $factory = null,
    ?CMSApplication $app = null,
    ?Input $input = null,
  ) {
    parent::__construct($config, $factory, $app, $input);

    $this->controllerSetup();
  }

  public function process()
  {
    $filter = $this->app->getInput()->get('filter', '', 'string');

    $event = array_key_exists('event', $filter) ? $filter['event'] : Aliases::current();

    if (!EventInfo::isValidEventAlias($event)) {
      $this->setRedirect(
        'index.php?option=com_claw&view=sponsorships',
        'Event selection not valid for deployment.',
        'error'
      );

      return false;
    }

    $deploy = new Deploy($event, Deploy::SPONSORSHIPS);
    $log = $deploy->deploy();
    echo $log;
  }
}
