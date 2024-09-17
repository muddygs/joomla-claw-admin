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

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Input\Input;
use Joomla\CMS\Router\Route;
use ClawCorpLib\Traits\Controller;

/**
 * Sponsors list controller class.
 *
 * @since  1.6
 */
class SponsorsController extends AdminController
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

  public function eblast()
  {
    /** @var ClawCorp\Component\Claw\Administrator\Model\SponsorsModel */
    $model = parent::getModel($this->name, 'Administrator', ['ignore_request' => true]);

    $model->eblast();

    $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
    return true;
  }
}
