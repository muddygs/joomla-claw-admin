<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */


namespace ClawCorp\Component\Claw\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Input\Input;
use ClawCorpLib\Traits\Controller;
use ClawCorpLib\Grid\Deploy;
use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Lib\EventInfo;

/**
 * Shifts list controller class.
 */
class ShiftsController extends AdminController
{
  use Controller;

  public function __construct(
    $config = [],
    ?MVCFactoryInterface $factory = null,
    ?CMSApplication $app = null,
    ?Input $input = null,
    ?FormFactoryInterface $formFactory = null
  ) {
    parent::__construct($config, $factory, $app, $input, $formFactory);

    $this->controllerSetup();
  }

  public function process()
  {
    $filter = $this->app->getInput()->get('filter', '', 'string');

    $eventAlias = array_key_exists('event', $filter) ? $filter['event'] : Aliases::current();
    $eventInfo = new EventInfo($eventAlias);

    $grid = new Deploy($eventInfo);
    $grid->createEvents();
    $logs = $grid->createEvents();
    $orphans = $grid->FindOrphanedShiftEvents();
    self::displayLogs($logs, $orphans);
  }

  private function displayLogs(&$logs, &$orphans)
  {
    /** @var \ClawCorp\Component\Claw\Administrator\View\Shifts\DeployLog */
    $view = $this->getView('Shifts', 'DeployLog');
    $view->setModel($this->model, true);
    $view->logs = $logs;
    $view->orphans = $orphans;

    $view->display();
  }
}
