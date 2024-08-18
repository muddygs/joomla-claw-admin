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

use ClawCorpLib\Grid\Grids;
use ClawCorpLib\Lib\Aliases;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Input\Input;
use ClawCorpLib\Traits\Controller;

/**
 * Shifts list controller class.
 */
class ShiftsController extends AdminController
{
  use Controller;

  public function __construct(
    $config = [],
    MVCFactoryInterface $factory = null,
    ?CMSApplication $app = null,
    ?Input $input = null,
    FormFactoryInterface $formFactory = null
  ) {
    parent::__construct($config, $factory, $app, $input, $formFactory);

    $this->controllerSetup();
  }

  public function process()
  {
    $filter = $this->app->getInput()->get('filter', '', 'string');

    $event = array_key_exists('event', $filter) ? $filter['event'] : Aliases::current();

    $grid = new Grids($event);
    $grid->createEvents();
  }

  // TODO: Implement this method
  // public function reset()
  // {
  // 	$filter = $this->app->getInput()->get('filter', '', 'string');

  // 	$event = array_key_exists('event', $filter) ? $filter['event'] : Aliases::current();

  // 	$grid = new Grids($event);
  // 	$grid->resetEvents();
  // }

  /**
   * Proxy for getModel.
   *
   * @param   string  $name    The model name. Optional.
   * @param   string  $prefix  The class prefix. Optional.
   * @param   array   $config  The array of possible config values. Optional.
   *
   * @return  \ClawCorp\Component\Claw\Administrator\Model\ShiftsModel
   *
   * @since   1.6
   */
}
