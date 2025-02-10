<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2025 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Site\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use ClawCorpLib\Traits\Controller;
use ClawCorpLib\Lib\Jwtwrapper;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Input\Input;

class BadgeprintController extends BaseController
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

    $token = $this->input->get('token', '');

    if (!Jwtwrapper::valid(page: 'badge-print', token: $token)) {
      echo 'invalid token';
      return;
    }
  }

  public function print()
  {
    //* @var \ClawCorp\Component\Claw\Site\View\Badgeprint\RawView */
    $view = $this->getView('badgeprint', 'raw');

    $view->action = $this->input->get('action', '', 'string');
    $view->registrationCode = trim($this->input->get('registration_code', '', 'string'));
    $view->token = $this->input->get('token', '', 'string');
    $view->page = 'badge-print';
    $view->quantity = $this->input->get('quantity', 0, 'uint');
    $view->type = $this->input->get('type', 0, 'uint');

    $view->display();
  }
}
