<?php
/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\Controller;

defined('_JEXEC') or die;

use ClawCorpLib\Traits\Controller;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Input\Input;

/**
 * Example Input data post:
 * data:
data: array(9)
  option: "com_claw"
  task: "getEmailStatusTest"
  format: "raw"
  jform: array(4)
    event: "c0424"
    quantity: "1"
    packageid: "123"
    owner-fields: array(1)
      owner-fields0: array(2)
        owner_name: "name_xxx"
        owner_email: "xxx"
  addon-D: 11
  addon-B: 22
  htmxChangedField: "jform[owner-fields][owner-fields0][owner_email]"
  controller: "coupongenerator"
*/

/*
 * @package ClawCorp\Component\Claw\Administrator\Controller
 */

class CoupongeneratorController extends FormController
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

  public function packageOptions()
  {
    $this->checkToken();

    /** @var \ClawCorp\Component\Claw\Administrator\View\Coupongenerator\HtmxAddonsView */
    $view = $this->getView('Coupongenerator', 'HtmxPackages');
    $view->setModel($this->model, true);
    $view->input = $this->data;

    $view->display();
  }

  public function addonCheckboxes()
  {
    $this->checkToken();

    /** @var \ClawCorp\Component\Claw\Administrator\View\Coupongenerator\HtmxAddonsView */
    $view = $this->getView('Coupongenerator', 'HtmxAddons');
    $view->setModel($this->model, true);
    $view->input = $this->input->get('jform', [], 'array');

    $view->display();
  }

  public function couponValue()
  {
    $this->checkToken();

    // Handle dynamically created checkboxes
    foreach ( $this->input->post->getArray() AS $key => $value ) {
      if ( str_starts_with($key, 'addon-') && strlen($key) == 7 ) {
        $this->data[$key] = $this->input->getString($key, '');
      }
    }

    /** @var \ClawCorp\Component\Claw\Administrator\View\Coupongenerator\HtmxCouponView */
    $view = $this->getView('Coupongenerator', 'HtmxCoupon');
    $view->setModel($this->model, true);
    $view->input = $this->data;

    $view->display();
  }

  public function createCoupons()
  {
    $this->checkToken();

    // Handle dynamically created checkboxes
    foreach ( $this->input->post->getArray() AS $key => $value ) {
     if ( str_starts_with($key, 'addon-') && strlen($key) == 7 ) {
       $this->data[$key] = $this->input->getString($key, '');
     }
   }

    $this->data['emailOverride'] = $this->input->get('emailOverride', 0, 'int');
    // End checkboxes

    /** @var \ClawCorp\Component\Claw\Administrator\View\Coupongenerator\HtmxGenerateView */
    $view = $this->getView('Coupongenerator', 'HtmxGenerate');
    $view->setModel($this->model, true);
    $view->input = $this->data;

    $view->display();
  }

  public function emailStatus()
  {
    $this->checkToken();

    /** @var \ClawCorp\Component\Claw\Administrator\View\Coupongenerator\HtmxCouponView */
    $view = $this->getView('Coupongenerator', 'HtmxEmailStatus');
    $view->setModel($this->model, true);
    $view->input = $this->data;

    $view->display();
  }

  public function getEmailOwnerStatus()
  {
    // Check for request forgeries.
    $this->checkToken();

    $this->data['htmxChangedField'] = $this->input->get('htmxChangedField', '', 'string');
    $this->data['emailOverride'] = $this->input->get('emailOverride', 0, 'int');

    $status = $this->model->emailStatus($this->data);

    header('Content-Type: text/html');
    echo $status->error ? '<span class="fa fa-ban text-danger"></span>' : '<span class="fa fa-check"></span>';
  }

}