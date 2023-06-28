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

    // $view = $input->get('view');
    // $task = $input->get('task');
    // $id = $input->get('id');

    // switch ($task) {
    //   case 'copy':
    //     $this->copy();
    //     break;

    //   case 'validatecoupon':
    //     //$this->RegistrationSurveyProcess();
    //     break;

    //   default:
    //     # code...
    //     break;
    // }

  }

  // TODO: Is this supposed to be here, or it is somewhere else and this is a copy/paste error?
  public function copy()
  {
    $view = $this->input->get('view');
    $task = $this->input->get('task');
    $id = $this->input->get('id');

    switch ($view) {
      case 'skillsubmission':
        switch ($task) {
          case 'copy':
            echo "copy task for skillsubmission id $id";
            /** @var ClawCorp\Component\Claw\Site\Model\SkillsubmissionModel */
            $siteModel = $this->getModel('Skillsubmission', 'Site');
            $siteModel->duplicate($id);

            break;

          default:
            # code...
            break;
        }
    }
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

    /** @var ClawCorp\Component\Claw\Site\Model\RegistrationsurveyModel */
    $siteModel = $this->getModel('Registrationsurvey');
    $json = $siteModel->RegistrationSurveyCouponStatus($coupon);

    header('Content-Type: application/json');
    echo $json;
  }

}
