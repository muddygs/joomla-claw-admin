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
use ClawCorpLib\Traits\Controller;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Input\Input;
use ClawCorpLib\Lib\Registrant;

class RollcallController extends BaseController
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

    if (!Jwtwrapper::valid(page: 'volunteer-roll-call', token: $token)) {
      echo 'invalid token';
      return;
    }
  }

  public function rollcallSearch()
  {
    $regid = $this->input->get('regid', '');

    /** @var \ClawCorp\Component\Claw\Site\View\Rollcall\HtmxSearchView */
    $view = $this->getView('Rollcall', 'HtmxSearch');
    $view->setModel($this->model, true);
    $view->regid = $regid;

    $view->display();
  }

  public function rollcallOverview()
  {
    /** @var \ClawCorp\Component\Claw\Site\View\Rollcall\HtmxOverviewView */
    $view = $this->getView('Rollcall', 'HtmxOverview');
    $view->setModel($this->model, true);

    $view->display();
  }

  public function rollcallToggle()
  {
    $hxTrigger = $this->input->server->get('HTTP_HX_TRIGGER', '');
    $regid  = Helpers::sessionGet('rollcallRegid');

    if (empty($hxTrigger) || !(str_starts_with($hxTrigger, 'in-') || str_starts_with($hxTrigger, 'out-'))) {
      echo 'invalid trigger';
      return;
    }

    if (empty($regid)) {
      echo 'invalid regid';
      return;
    }

    $isCheckin = str_starts_with($hxTrigger, 'in-');
    $action = $this->input->get('action', false);

    $recordId = (int)(explode('-', $hxTrigger)[1]);

    /** @var \ClawCorp\Component\Claw\Site\Model\RollcallModel */
    $siteModel = $this->getModel('Rollcall');
    $result = $siteModel->volunteerUpdate(recordId: $recordId, isCheckin: $isCheckin, action: $action);

    if (!$result) {
      echo 'Update failed. Reload page to continue.';
      return;
    }

    /** @var \ClawCorp\Component\Claw\Site\View\Rollcall\HtmxSearchView */
    $view = $this->getView('Rollcall', 'HtmxSearch');
    $view->setModel($this->model, true);
    $view->regid = $regid;

    $view->display();
  }

  public function rollcallAddShift()
  {
    $regid  = Helpers::sessionGet('rollcallRegid', 0);
    $eventid = $this->input->get('eventid', 0);

    if (empty($regid)) {
      echo 'invalid regid';
      return;
    }

    if (empty($eventid)) {
      echo 'invalid eventid';
      return;
    }

    $uid = Registrant::getUserIdFromInvoice($regid);

    /** @var \ClawCorp\Component\Claw\Site\Model\RollcallModel */
    $siteModel = $this->getModel('Rollcall');
    $result = $siteModel->volunteerAddShift(uid: $uid, eventid: $eventid);

    if (!$result) {
      echo "Error during shift add";
      return;
    }

    /** @var \ClawCorp\Component\Claw\Site\View\Rollcall\HtmxSearchView */
    $view = $this->getView('Rollcall', 'HtmxSearch');
    $view->setModel($this->model, true);
    $view->regid = $regid;

    // HTMX: Trigger refresh of the overview data
    $this->app->setHeader('HX-Trigger', 'updateOverview');

    $view->display();
  }
}
