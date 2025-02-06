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

use ClawCorpLib\Enums\JwtStates;
use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Lib\Jwtwrapper;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
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

  /**
   * Process the registration survey form's coupon field
   *
   * @return void
   */
  public function validatecoupon()
  {
    $this->checkToken();

    $json = new Json();
    $coupon = $json->get('coupon', '', 'string');

    /** @var \ClawCorp\Component\Claw\Site\Model\RegistrationsurveyModel */
    $siteModel = $this->getModel('Registrationsurvey');
    $json = $siteModel->RegistrationSurveyCouponStatus($coupon);

    header('Content-Type: application/json');
    echo $json;
  }

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
}
