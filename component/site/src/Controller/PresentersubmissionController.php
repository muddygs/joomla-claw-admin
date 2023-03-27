<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2022 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */


namespace ClawCorp\Component\Claw\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\Input\Input;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

use ClawCorp\Component\Claw\Administrator\Model\PresenterModel;

/**
 * Controller for a single sponsor record
 *
 * @since  1.6
 */
class PresentersubmissionController extends FormController
{
  public function __construct(
    $config = [],
    MVCFactoryInterface $factory = null,
    ?CMSApplication $app = null,
    ?Input $input = null,
    FormFactoryInterface $formFactory = null
  ) {
    parent::__construct($config, $factory, $app, $input, $formFactory);

    $task = $input->getCmd('task');

    if ( $task == 'submit' && $input != null )
    {
      $this->save();
    }
  }

  public function save($key = null, $urlVar = null)
  {
    // Check for request forgeries.
    $this->checkToken();

    /** @var ClawCorp\Component\Claw\Administrator\Model\PresenterModel */    
    $adminModel = $this->getModel('Presenter','Administrator');
    
    $app = Factory::getApplication();
    $input = $app->input;
    $data = $input->get('jform', array(), 'array');

    // Setup items not included in site model
    $data['uid'] = $app->getIdentity()->id;
    $data['id'] = $input->get('id',0,'int');

    if ( $data['id'] == 0 ) {
      $data['published'] = 3; // New submission
    }
    
    $result = $adminModel->save($data);
    
    return $result;
  }
}
