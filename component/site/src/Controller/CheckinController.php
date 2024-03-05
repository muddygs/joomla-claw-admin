<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\Input\Json;

class CheckinController extends BaseController
{
  public function search()
  {
    $this->checkToken();

    $json = new Json();
    $token = $json->get('token', '', 'string');
    $search = $json->get('search', '', 'string');
    $page = $json->get('page', '', 'string');

    /** @var \ClawCorp\Component\Claw\Site\Model\CheckinModel */
    $siteModel = $this->getModel();
    $result = $siteModel->JwtSearch(token: $token, search: $search, page: $page);
    header('Content-Type: application/json');
    echo json_encode($result);
  }

  public function value()
  {
    $this->checkToken();

    $json = new Json();
    $token = $json->get('token', '', 'string');
    $search = $json->get('registration_code', '', 'string');
    $page = $json->get('page', '', 'string');

    /** @var \ClawCorp\Component\Claw\Site\Model\CheckinModel */
    $siteModel = $this->getModel();
    $result = $siteModel->JwtValue(token: $token, registration_code: $search, page: $page);
    header('Content-Type: application/json');
    echo json_encode($result);
  }

  public function print()
  {
    //* @var \ClawCorp\Component\Claw\Site\View\Badgeprint\RawView */
    $view = $this->getView('badgeprint', 'raw');

    $view->action = $this->input->get('action', '', 'string');
    $view->registrationCode = trim($this->input->get('registration_code', '', 'string'));
    $view->token = $this->input->get('token', '', 'string');
    $view->page = $this->input->get('page', '', 'string');
    $view->quantity = $this->input->get('quantity', 0, 'uint');
    $view->type = $this->input->get('type', 0, 'uint');

    $view->display();
  }

  public function issue()
  {
    $this->checkToken();

    $json = new Json();
    $token = $json->get('token', '', 'string');
    $registration_code = $json->get('registration_code', '', 'string');
    $page = $json->get('page', '', 'string');

    /** @var \ClawCorp\Component\Claw\Site\Model\CheckinModel */
    $siteModel = $this->getModel();
    $result = $siteModel->JwtCheckin(token: $token, registration_code: $registration_code, page: $page);
    header('Content-Type: application/json');
    echo json_encode($result);
  }

  public function count()
  {
    $this->checkToken();

    $array = new Json();
    $token = $array->get('token', '', 'string');

    /** @var \ClawCorp\Component\Claw\Site\Model\CheckinModel */
    $siteModel = $this->getModel();
    $array = $siteModel->JwtGetCount(token: $token);

    header('Content-Type: application/json');
    echo json_encode($array);
  }

}