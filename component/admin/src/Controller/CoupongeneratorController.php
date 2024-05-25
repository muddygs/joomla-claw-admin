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

use Joomla\CMS\MVC\Controller\FormController;

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
  addon-D: "D"
  addon-B: "B"
  htmxChangedField: "jform[owner-fields][owner-fields0][owner_email]"
  helix_id: 9
  controller: "coupongenerator"
*/

/*
 * @package ClawCorp\Component\Claw\Administrator\Controller
 */

class CoupongeneratorController extends FormController
{
  public function packageOptions()
  {
    $this->checkToken();

    /** @var \ClawCorp\Component\Claw\Administrator\Model\CoupongeneratorModel */
    $model = $this->getModel('Coupongenerator');

    /** @var \ClawCorp\Component\Claw\Administrator\View\Coupongenerator\HtmxAddonsView */
    $view = $this->getView('Coupongenerator', 'HtmxPackages');
    $view->setModel($model, true);
    $view->input = $this->input->get('jform', [], 'array');

    $view->display();
  }

  public function addonCheckboxes()
  {
    $this->checkToken();

    /** @var \ClawCorp\Component\Claw\Administrator\Model\CoupongeneratorModel */
    $model = $this->getModel('Coupongenerator');

    /** @var \ClawCorp\Component\Claw\Administrator\View\Coupongenerator\HtmxAddonsView */
    $view = $this->getView('Coupongenerator', 'HtmxAddons');
    $view->setModel($model, true);
    $view->input = $this->input->get('jform', [], 'array');

    $view->display();
  }

  public function couponValue()
  {
    // Check for request forgeries.
    $this->checkToken();

    /** @var \ClawCorp\Component\Claw\Administrator\Model\CoupongeneratorModel */
    $model = $this->getModel('Coupongenerator');

    // Because checkboxes are not create by form template, we'll need to manually include in $input
    $input = $this->input->get('jform', [], 'array');

    foreach ( $this->input->post->getArray() AS $key => $value ) {
      if ( str_starts_with($key, 'addon-') && strlen($key) == 7 ) {
        $input[$key] = $this->input->getString($key, '');
      }
    }

    /** @var \ClawCorp\Component\Claw\Administrator\View\Coupongenerator\HtmxCouponView */
    $view = $this->getView('Coupongenerator', 'HtmxCoupon');
    $view->setModel($model, true);
    $view->input = $input;

    $view->display();
  }

  public function createCoupons()
  {
    // Check for request forgeries.
    $this->checkToken();

    /** @var \ClawCorp\Component\Claw\Administrator\Model\CoupongeneratorModel */
    $model = $this->getModel('Coupongenerator');

    // Because checkboxes are not create by form template, we'll need to manually include in $input
    $input = $this->input->get('jform', [], 'array');

    foreach ( $this->input->post->getArray() AS $key => $value ) {
      if ( str_starts_with($key, 'addon-') && strlen($key) == 7 ) {
        $input[$key] = $this->input->getString($key, '');
      }
    }

    $input['emailOverride'] = $this->input->get('emailOverride', 0, 'int');

    /** @var \ClawCorp\Component\Claw\Administrator\View\Coupongenerator\HtmxGenerateView */
    $view = $this->getView('Coupongenerator', 'HtmxGenerate');
    $view->setModel($model, true);
    $view->input = $input;

    $view->display();
  }

  public function emailStatus()
  {
    // Check for request forgeries.
    $this->checkToken();

    /** @var \ClawCorp\Component\Claw\Administrator\Model\CoupongeneratorModel */
    $model = $this->getModel('Coupongenerator');

    /** @var \ClawCorp\Component\Claw\Administrator\View\Coupongenerator\HtmxCouponView */
    $view = $this->getView('Coupongenerator', 'HtmxEmailStatus');
    $view->setModel($model, true);
    $view->input = $this->input->get('jform', [], 'array');

    $view->display();
  }

  public function getEmailOwnerStatus()
  {
    // Check for request forgeries.
    $this->checkToken();

    $input = $this->input->get('jform', [], 'array');
    $input['htmxChangedField'] = $this->input->get('htmxChangedField', '', 'string');
    $input['emailOverride'] = $this->input->get('emailOverride', 0, 'int');

    /** @var \ClawCorp\Component\Claw\Administrator\Model\CoupongeneratorModel */
    $model = $this->getModel('Coupongenerator');
    $status = $model->emailStatus($input);

    header('Content-Type: text/html');
    echo $status->error ? '<span class="fa fa-ban text-danger"></span>' : '<span class="fa fa-check"></span>';
  }

}