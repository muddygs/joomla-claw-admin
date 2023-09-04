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

  public function __construct($config = [], MVCFactoryInterface $factory = null, $app = null, $input = null)
  {
    parent::__construct($config, $factory, $app, $input);
  }

  public function display($cachable = false, $urlparams = array())
  {
    return parent::display($cachable, $urlparams);
  }

#region Coupon Tasks
  public function couponLoadEvent()
  {
    // TODO: Need to validate checkToken on ALL controllers
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
//    echo $text;
  }
/*
	case 'populate':
		$invoice = trim($app->input->get('invoice', '', 'string'));
		if ( $invoice ) getInvoices($invoice);
		break;

	case 'refund':
		$transaction = trim($app->input->get('transaction', 0, 'int'));
		$amount = trim($app->input->get('amount', 0.0, 'double'));
		$cancelall = trim($app->input->get('cancelall', 'false', 'string'));
		if ($transaction < 1 || $amount < 1) return '<pre>Check transaction selection and/or amount</pre>';

		process($transaction, $amount, $cancelall == 'false' ? false : true);
		break;

	case 'charge':
		$regcode = trim($app->input->get('regcode', '', 'string'));
		$amount = trim($app->input->get('amount', 0.0, 'double'));
		$receiptText = trim($app->input->get('receipt', 'CLAW Refund', 'string'));
		if ($regcode == '' || $amount < 1) return '<pre>Check transaction selection and/or amount</pre>';

		processProfileCharge($regcode, $amount, $receiptText);
		break;
*/
#endregion Refunds
}
