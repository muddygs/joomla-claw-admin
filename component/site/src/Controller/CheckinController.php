<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2025 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Site\Controller;

defined('_JEXEC') or die;

use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Lib\Jwtwrapper;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\Input\Json;
use ClawCorpLib\Traits\Controller;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Input\Input;
use ClawCorpLib\Lib\Registrant;

class CheckinController extends BaseController
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

  public function rollcallSearch()
  {
    $input = $this->input;
    $token = $input->get('token', '');
    $regid = $input->get('regid', '');

    if (!Jwtwrapper::valid(page: 'volunteer-roll-call', token: $token)) {
      echo 'invalid token';
      return;
    }

    /** @var \ClawCorp\Component\Claw\Site\View\Checkin\HtmxSearchView */
    $view = $this->getView('Checkin', 'HtmxSearch');
    $view->setModel($this->model, true);
    $view->regid = $regid;

    $view->display();
  }

  public function rollcallOverview()
  {
    $input = $this->input;
    $token = $input->get('token', '');

    if (!Jwtwrapper::valid(page: 'volunteer-roll-call', token: $token)) {
      echo 'invalid token';
      return;
    }

    /** @var \ClawCorp\Component\Claw\Site\View\Checkin\HtmxOverviewView */
    $view = $this->getView('Checkin', 'HtmxOverview');
    $view->setModel($this->model, true);

    $view->display();
  }

  public function rollcallToggle()
  {
    $input = $this->input;
    $token = $input->get('token', '');
    $hxTrigger = $input->server->get('HTTP_HX_TRIGGER', '');
    $regid  = Helpers::sessionGet('rollcallRegid');

    if (!Jwtwrapper::valid(page: 'volunteer-roll-call', token: $token)) {
      echo 'invalid token';
      return;
    }

    if (empty($hxTrigger) || !(str_starts_with($hxTrigger, 'in-') || str_starts_with($hxTrigger, 'out-'))) {
      echo 'invalid trigger';
      return;
    }

    if (empty($regid)) {
      echo 'invalid regid';
      return;
    }

    $isCheckin = str_starts_with($hxTrigger, 'in-');
    $action = $input->get('action', false);

    $recordId = (int)(explode('-', $hxTrigger)[1]);

    /** @var \ClawCorp\Component\Claw\Site\Model\CheckinModel */
    $siteModel = $this->getModel('Checkin');
    $result = $siteModel->volunteerUpdate(recordId: $recordId, isCheckin: $isCheckin, action: $action);

    if (!$result) {
      echo 'Update failed. Reload page to continue.';
      return;
    }

    /** @var \ClawCorp\Component\Claw\Site\View\Checkin\HtmxSearchView */
    $view = $this->getView('Checkin', 'HtmxSearch');
    $view->setModel($this->model, true);
    $view->regid = $regid;

    $view->display();
  }

  public function rollcallAddShift()
  {
    $input = $this->input;
    $token = $input->get('token', '');
    $regid  = Helpers::sessionGet('rollcallRegid', 0);
    $eventid = $input->get('eventid', 0);

    if (!Jwtwrapper::valid(page: 'volunteer-roll-call', token: $token)) {
      echo 'invalid token';
      return;
    }

    if (empty($regid)) {
      echo 'invalid regid';
      return;
    }

    if (empty($eventid)) {
      echo 'invalid eventid';
      return;
    }

    $uid = Registrant::getUserIdFromInvoice($regid);

    /** @var \ClawCorp\Component\Claw\Site\Model\CheckinModel */
    $siteModel = $this->getModel('Checkin');
    $result = $siteModel->volunteerAddShift(uid: $uid, eventid: $eventid);

    if (!$result) {
      echo "Error during shift add";
      return;
    }

    /** @var \ClawCorp\Component\Claw\Site\View\Checkin\HtmxSearchView */
    $view = $this->getView('Checkin', 'HtmxSearch');
    $view->setModel($this->model, true);
    $view->regid = $regid;

    // HTMX: Trigger refresh of the overview data
    $this->app->setHeader('HX-Trigger', 'updateOverview');

    $view->display();
  }

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
