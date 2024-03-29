<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2022 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Input\Json;

/**
 * Default Controller of Claw component
 */

class DisplayController extends BaseController
{
  /**
   * The default view for the display method.
   *
   * @var string
   */
  protected $default_view = 'claw';

  protected function createModel($name, $prefix = '', $config = [])
  {
    $config['context'] = implode('.', [
      $this->input->get('option', 'com_claw'),
      $this->input->get('layout', 'edit'),
      $this->input->get('view', 'claw')
    ]);
    
    return parent::createModel($name, $prefix, $config);
  }

#region Coupon Tasks
  public function couponLoadEvent()
  {
    // Check for request forgeries.
    $this->checkToken();

    $json = new Json();
    /** @var \ClawCorp\Component\Claw\Administrator\Model\CoupongeneratorModel */
    $model = $this->getModel('Coupongenerator');
    [$events, $addons] = $model->populateCodeTypes($json);
    header('Content-Type: application/json');
    echo json_encode([$events, $addons]);
  }

  public function couponValue()
  {
    // Check for request forgeries.
    $this->checkToken();

    $json = new Json();
    /** @var \ClawCorp\Component\Claw\Administrator\Model\CoupongeneratorModel */
    $model = $this->getModel('Coupongenerator');
    $value = $model->couponValue($json);
    header('Content-Type: text/plain');
    echo $value;
  }

  public function createCoupons()
  {
    // Check for request forgeries.
    $this->checkToken();

    $json = new Json();
    /** @var \ClawCorp\Component\Claw\Administrator\Model\CoupongeneratorModel */
    $model = $this->getModel('Coupongenerator');
    $model->createCoupons($json);
  }

  public function emailStatus()
  {
    // Check for request forgeries.
    $this->checkToken();

    $json = new Json();
    /** @var \ClawCorp\Component\Claw\Administrator\Model\CoupongeneratorModel */
    $model = $this->getModel('Coupongenerator');
    $status = $model->emailStatus($json);
    header('Content-Type: text/plain');
    echo $status->msg;
  }
#endregion Coupon Tasks

#region Refunds
  public function refundProcessRefund()
  {
    $this->checkToken();

    $json = new Json();
    /** @var \ClawCorp\Component\Claw\Administrator\Model\RefundsModel */
    $model = $this->getModel('Refunds');
    $text = $model->refundProcessRefund($json);
    header('Content-Type: text/plain');
    echo $text;
  }

  public function refundChargeProfile()
  {
    $this->checkToken();

    $json = new Json();
    /** @var \ClawCorp\Component\Claw\Administrator\Model\RefundsModel */
    $model = $this->getModel('Refunds');
    $text = $model->refundChargeProfile($json);
    header('Content-Type: text/plain');
    echo $text;
  }

  public function refundPopulate()
  {
    $this->checkToken();

    $json = new Json();
    /** @var \ClawCorp\Component\Claw\Administrator\Model\RefundsModel */
    $model = $this->getModel('Refunds');
    header('Content-Type: text/plain');
    $model->refundPopulate($json);
  }
#endregion Refunds

#region Copy/Create Events
  public function doCopyEvent()
  {
    $this->checkToken();

    $json = new Json();
    /** @var \ClawCorp\Component\Claw\Administrator\Model\EventcopyModel */
    $model = $this->getModel('Eventcopy');
    $text = $model->doCopyEvent($json);
    header('Content-Type: text/plain');
    echo $text;
  }
#endregion Copy Event
}
