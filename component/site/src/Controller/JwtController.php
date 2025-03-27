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
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Input\Input;
use Joomla\Input\Json;
use ClawCorpLib\Traits\Controller;
use Joomla\CMS\Router\Route;

class JwtController extends BaseController
{
  use Controller;

  protected $app;
  protected $post;

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

  /**
   * Called by jwt.js when the state of a new token changes to issued
   */
  public function issue()
  {
    $token = $this->input->get('token', '');
    $jwt = new Jwtwrapper();
    $payload = $jwt->loadFromToken($token);

    if (!is_null($payload) && array_key_exists($payload->subject, Jwtwrapper::jwt_token_pages)) {
      $view = Jwtwrapper::jwt_token_pages[$payload->subject]['view'];
      $route = Route::_("/index.php?option=com_claw&view=$view&token=$token");
    } else {
      $this->app->enqueueMessage('Permission denied.', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
      $route = Route::_('/');
    }

    $this->setRedirect($route);
  }

  public function jwtstateInit()
  {
    $this->checkToken();

    $json = new Json();
    $email = $json->get('email', '', 'string');
    $url = $json->get('urlInput', '', 'string');

    Helpers::sessionSet('jwt_url', $url);

    /** @var \ClawCorp\Component\Claw\Site\Model\JwtModel */
    $siteModel = $this->getModel();
    $json = $siteModel->JwtstateInit(email: $email, subject: $url);

    header('Content-Type: application/json');
    echo $json;
  }

  public function jwtstateState()
  {
    $this->checkToken();

    $url = Helpers::sessionGet('jwt_url');

    /** @var \ClawCorp\Component\Claw\Site\Model\JwtModel */
    $siteModel = $this->getModel('Jwt');
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
    /** @var \ClawCorp\Component\Claw\Site\Model\JwtModel */
    $siteModel = $this->getModel('Jwt');
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
    $this->checkToken();

    $token = $this->input->get('token', '', 'string');
    /** @var \ClawCorp\Component\Claw\Site\Model\JwtModel */
    $siteModel = $this->getModel('Jwt');
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
    /** @var \ClawCorp\Component\Claw\Site\Model\JwtModel */
    $siteModel = $this->getModel('Jwt');
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

}
