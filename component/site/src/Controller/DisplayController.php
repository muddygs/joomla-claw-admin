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
use ClawCorpLib\Lib\Jwtwrapper;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Router\Route;
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

    if ($this->input == null) {
      $this->input = $this->app->input;
    }
  }

  public function copyskill()
  {
    $id = $this->input->get('id');
    /** @var \ClawCorp\Component\Claw\Site\Model\SkillsubmissionModel */
    $siteModel = $this->getModel('Skillsubmission', 'Site');
    [$status, $data] = $siteModel->duplicate($id);

    // Email via admin model
    /** @var \ClawCorp\Component\Claw\Administrator\Model\SkillModel */
    $adminModel = $this->getModel('Skill', 'Administrator');
    if ($status) $adminModel->email(true, $data);

    $skillRoute = Route::_('index.php?option=com_claw&view=skillssubmissions');
    $this->setRedirect($skillRoute);

    return true;
  }


  public function copybio()
  {
    $id = $this->input->get('id');
    /** @var \ClawCorp\Component\Claw\Site\Model\PresentersubmissionModel */
    $siteModel = $this->getModel('Presentersubmission', 'Site');
    $siteModel->duplicate($id);

    return true;
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
    $json = $siteModel->JwtstateInit(email: $email, subject: $url);

    header('Content-Type: application/json');
    echo $json;
  }

  public function jwtstateState()
  {
    $this->checkToken();

    $url = Helpers::sessionGet('jwt_url', '');

    /** @var \ClawCorp\Component\Claw\Site\Model\CheckinModel */
    $siteModel = $this->getModel('Checkin');
    $json = $siteModel->JwtstateState(subject: $url);

    header('Content-Type: application/json');
    echo json_encode($json);
  }

  #region email token response processing
  /**
   * Process token confirmation from email link
   *
   * @return void
   */
  public function jwtconfirm()
  {
    $token = $this->input->get('token', '', 'string');
    /** @var \ClawCorp\Component\Claw\Site\Model\CheckinModel */
    $siteModel = $this->getModel('Checkin');
    $json = $siteModel->JwtConfirm(token: $token);

    header('Content-Type: application/json');
    echo $json;
  }

  /**
   * Process token revocation from email link
   *
   * @return void
   */
  public function jwtrevoke()
  {
    $token = $this->input->get('token', '', 'string');
    /** @var \ClawCorp\Component\Claw\Site\Model\CheckinModel */
    $siteModel = $this->getModel('Checkin');
    $json = $siteModel->JwtRevoke(token: $token);

    header('Content-Type: application/json');
    echo $json;
  }
  #endregion

  #region jwtmon
  public function jwtTokenCheck()
  {
    $this->checkToken();

    $json = new Json();
    $token = $json->get('token', '', 'string');
    /** @var \ClawCorp\Component\Claw\Site\Model\CheckinModel */
    $siteModel = $this->getModel('Checkin');
    $result = $siteModel->JwtmonValidate(token: $token);

    header('Content-Type: application/json');
    echo json_encode($result);
  }
  #endregion

  #region jwt_dashboard
  public function jwtdashboardConfirm(JwtStates $state = JwtStates::issued)
  {
    $this->checkToken();

    $result = ['id' => 0];

    $json = new Json();
    $id = $json->get('tokenid', [], 'int');

    // Verify user permissions
    $user = $this->app->getIdentity();
    if ($user->authorise('core.admin') && $id > 0) {
      $return = Jwtwrapper::setDatabaseState($id, $state);
      if ($return) $result['id'] = $id;
    }

    header('Content-Type: application/json');
    echo json_encode($result);
  }

  public function jwtdashboardRevoke()
  {
    $this->jwtdashboardConfirm(JwtStates::revoked);
  }

  #endregion

  public function mealCheckin()
  {
    $this->checkToken();

    $json = new Json();
    $token = $json->get('token', '', 'string');
    $search = $json->get('registration_code', '', 'string');
    $meal = $json->get('mealEvent', '', 'string');

    /** @var \ClawCorp\Component\Claw\Site\Model\CheckinModel */
    $siteModel = $this->getModel('Checkin');
    $result = $siteModel->JwtMealCheckin(token: $token, registration_code: $search, meal: $meal);
    header('Content-Type: application/json');
    echo json_encode($result);
  }

  public function volunteerSearch()
  {
    $this->checkToken();

    $json = new Json();
    $token = $json->get('token', '', 'string');
    $regid = $json->get('regid', '', 'string');

    /** @var \ClawCorp\Component\Claw\Site\Model\CheckinModel */
    $siteModel = $this->getModel('Checkin');
    $result = $siteModel->volunteerSearch(token: $token, regid: $regid);
    header('Content-Type: application/json');
    echo json_encode($result);
  }

  public function volunteerUpdate()
  {
    $this->checkToken();
    $json = new Json();
    $token = $json->get('token', '', 'string');
    $regid = $json->get('regid', '', 'uint');
    $action = $json->get('action', '', 'string');
    $currentValue = $json->get('currentValue', '', 'boolean');

    /** @var \ClawCorp\Component\Claw\Site\Model\CheckinModel */
    $siteModel = $this->getModel('Checkin');
    $result = $siteModel->volunteerUpdate(token: $token, regid: $regid, action: $action, currentValue: $currentValue);
    header('Content-Type: text/plain');
    echo $result;
  }

  public function volunteerAddShift()
  {
    $this->checkToken();

    $json = new Json();
    $token = $json->get('token', '', 'string');
    $uid = $json->get('uid', '', 'uint');
    $shift = $json->get('shift', '', 'string');

    /** @var \ClawCorp\Component\Claw\Site\Model\CheckinModel */
    $siteModel = $this->getModel('Checkin');
    $result = $siteModel->volunteerAddShift(token: $token, uid: $uid, shift: $shift);
    header('Content-Type: text/plain');
    echo $result;
  }
}
