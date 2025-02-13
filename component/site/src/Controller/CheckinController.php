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

use ClawCorpLib\Lib\Jwtwrapper;
use Joomla\CMS\MVC\Controller\BaseController;
use ClawCorpLib\Traits\Controller;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Input\Input;

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

  public function search()
  {
    $input = $this->input;
    $token = $input->get('token', '');
    $search = $input->get('search', '');
    $page = $input->get('page', '');

    if (!Jwtwrapper::valid(page: $page, token: $token)) {
      echo 'invalid token';
      return;
    }

    /** @var \ClawCorp\Component\Claw\Site\View\Checkin\HtmxSearchView */
    $view = $this->getView('Checkin', 'HtmxSearch');
    $view->setModel($this->model, true);
    $view->search = $search;
    $view->page = $page;

    $view->display();
  }

  public function value()
  {
    $input = $this->input;
    $token = $input->get('token', '');
    $page = $input->get('page', '');

    if (!Jwtwrapper::valid(page: $page, token: $token)) {
      echo 'invalid token';
      return;
    }

    $searchresults = $input->get('searchresults', '');

    /** @var \ClawCorp\Component\Claw\Site\View\Checkin\HtmxSearchView */
    $view = $this->getView('Checkin', 'HtmxRecord');
    $view->setModel($this->model, true);
    $view->search = $searchresults;
    $view->page = $page;

    $view->display();
  }

  public function issue()
  {
    $input = $this->input;
    $token = $input->get('token', '');
    $registration_code = $input->get('searchresults', '');
    $page = $input->get('page', '');

    if (!Jwtwrapper::valid(page: $page, token: $token)) {
      echo 'invalid token';
      return;
    }

    /** @var \ClawCorp\Component\Claw\Site\Model\CheckinModel */
    $siteModel = $this->getModel();
    $siteModel->JwtCheckin($registration_code);
    echo '<h1>Checkin Complete</h1>';
  }

  public function count()
  {
    // Since we have multiple forms, the count has counttoken instead of just token
    $token = $this->input->get('counttoken', '');

    if (!Jwtwrapper::valid(page: 'badge-print', token: $token)) {
      echo 'invalid token';
      return;
    }

    /** @var \ClawCorp\Component\Claw\Site\View\Checkin\PrintcountView */
    $view = $this->getView('Checkin', 'Printcount');
    $view->setModel($this->model, true);

    $view->display();
  }
}
