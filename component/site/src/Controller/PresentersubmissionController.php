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

use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Lib\Aliases;
use Joomla\CMS\Router\Route;

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

    /** @var \Joomla\CMS\MVC\Model\AdminModel */
    $siteModel = $this->getModel();
    $form = $siteModel->getForm();
    /** @var \Joomla\CMS\Application\SiteApplication */
    $app = Factory::getApplication();

    $input = $app->input;
    $data = $input->get('jform', [], 'array');
    $data = $form->filter($data);
    $validation = $siteModel->validate($form, $data);

    
    if ( $validation === false ) {
      Helpers::sessionSet('formdata', json_encode($data));
      $errors = $form->getErrors();

      foreach ( $errors AS $e ) {
        $app->enqueueMessage($e->getMessage(), \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
      }

      return false;
    }

    $files = $input->files->get('jform');

    if ( !$data['photo'] && !count($files['photo_upload']))
    {
      $photo = Helpers::sessionGet('photo');
      if ( !$photo ) {
        $app->enqueueMessage('A representative photo is required', \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
      }
      return false;
      
    }
    
    $identity = $app->getIdentity();

    // Setup items not included in site model
    $data['uid'] = $identity->id;
    $data['email'] = $identity->email;
    $data['id'] = $input->get('id',0,'int');

    // If it's not the current event, we want to clear the ID and create
    // a new record.

    if ( $data['event'] != Aliases::current() ) {
      $data['id'] = 0;
    }

    $data['event'] = Aliases::current();
    
    if ( $data['id'] == 0 ) {
      $data['published'] = 3; // New submission
    }
    
    /** @var ClawCorp\Component\Claw\Administrator\Model\PresenterModel */    
    $adminModel = $this->getModel('Presenter','Administrator');
    $result = $adminModel->save($data);

    if ( $result ) {
      // Redirect to the main submission page
      $this->setRedirect(Route::_('index.php?option=com_claw&view=skillssubmissions', 'Biography save successful.'));
      ;
    }
    return $result;
  }
}
