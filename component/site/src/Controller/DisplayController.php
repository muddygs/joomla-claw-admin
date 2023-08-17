<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Site\Controller;

defined('_JEXEC') or die;

use ClawCorpLib\Enums\JwtStates;
use ClawCorpLib\Helpers\Helpers;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Input\Input;
use Joomla\Input\Json;

class DisplayController extends BaseController
{
  protected $app;
  protected $post;

  public function __construct(
    $config = [],
    MVCFactoryInterface $factory = null,
    ?CMSApplication $app = null,
    ?Input $input = null,
    FormFactoryInterface $formFactory = null
  ) {
    // Call parent constructor before anything else to ensure all $this properties are set
    parent::__construct($config, $factory, $app, $input, $formFactory);

    Helpers::sessionSet('formdata', '');
    Helpers::sessionSet('photo', '');

    /** @var \Joomla\CMS\Application\SiteApplication */
    $this->app = Factory::getApplication();
    $menu = $this->app->getMenu()->getActive();
    Helpers::sessionSet('menuid', $menu->id);

    if ( $this->input == null ) {
      $this->input = $this->app->input;
    }
  }

  public function copyskill()
  {
//    $this->checkToken();

    $id = $this->input->get('id');
    /** @var \ClawCorp\Component\Claw\Site\Model\SkillsubmissionModel */
    $siteModel = $this->getModel('Skillsubmission', 'Site');
    $siteModel->duplicate($id);
  }


  public function copybio()
  {
//    $this->checkToken();

    $id = $this->input->get('id');
    /** @var \ClawCorp\Component\Claw\Site\Model\PresentersubmissionModel */
    $siteModel = $this->getModel('Presentersubmission', 'Site');
    $siteModel->duplicate($id);
  }

  /**
   * Process the registration survey form's coupon field
   *
   * @return void
   */
  public function validatecoupon()
  {
    // TODO: Need to validate checkToken on ALL controllers
    // Check for request forgeries.
    $this->checkToken();

    $json = new Json();
    $coupon = $json->get('coupon', '', 'string');

    /** @var \ClawCorp\Component\Claw\Site\Model\RegistrationsurveyModel */
    $siteModel = $this->getModel('Registrationsurvey');
    $json = $siteModel->RegistrationSurveyCouponStatus($coupon);

    header('Content-Type: application/json');
    echo $json;
  }

  public function jwtstateInit()
  {
    $this->checkToken();

    $json = new Json();
    $email = $json->get('email', '', 'string');
    $url = $json->get('urlInput', '', 'string');

    Helpers::sessionSet('jwt_url', $url);

    /** @var \ClawCorp\Component\Claw\Site\Model\CheckinModel */
    $siteModel = $this->getModel('Checkin');
    $json = $siteModel->JwtstateInit(email: $email, url: $url);

    header('Content-Type: application/json');
    echo $json;
  }

  public function jwtstateState()
  {
    $this->checkToken();

    $url = Helpers::sessionGet('jwt_url', '');

    /** @var \ClawCorp\Component\Claw\Site\Model\CheckinModel */
    $siteModel = $this->getModel('Checkin');
    $json = $siteModel->JwtstateState(url: $url);

    header('Content-Type: application/json');
    echo $json;
  }

  public function jwtconfirm()
  {
    $token = $this->input->get('token', '', 'string');
    /** @var \ClawCorp\Component\Claw\Site\Model\CheckinModel */
    $siteModel = $this->getModel('Checkin');
    $json = $siteModel->JwtConfirm(token: $token);
    
    header('Content-Type: application/json');
    echo $json;

  }

  public function jwtrevoke()
  {

  }

  public function checkinSearch() {}

  public function checkinValue() {}

  public function checkinIssue() {}

  public function checkingGetCount() {}

}
