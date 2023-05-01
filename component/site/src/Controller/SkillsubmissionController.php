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

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\Input\Input;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Lib\Aliases;

/**
 * Controller for a single skill record
 */
class SkillsubmissionController extends FormController
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

    if ($task == 'submit' && $input != null) {
      $this->save();
    }
  }

  public function save($key = null, $urlVar = null)
  {
    // Check for request forgeries.
    $this->checkToken();

    /** @var Joomla\CMS\MVC\Model\AdminModel */
    $siteModel = $this->getModel();
    $form = $siteModel->getForm();
    $app = Factory::getApplication();

    $input = $app->input;
    $data = $input->get('jform', [], 'array');
    $validation = $siteModel->validate($form, $data);

    if ($validation === false) {
      Helpers::sessionSet('formdata', json_encode($data));
      $errors = $form->getErrors();

      foreach ($errors as $e) {
        $app->enqueueMessage($e->getMessage(), \Joomla\CMS\Application\CMSApplicationInterface::MSG_ERROR);
      }

      return false;
    }

    // Setup items not included in site model
    $data['uid'] = $app->getIdentity()->id;
    $data['owner'] = $data['uid'];
    $data['id'] = $input->get('id', 0, 'int');
    $data['event'] = Aliases::current;
    $data['length_info'] = $input->get('length', 60, 'int');

    if (($data['id'] ?? 0) == 0 || !is_int($data['id'])) {
      $data['published'] = 3; // New submission
    }

    /** @var ClawCorp\Component\Claw\Administrator\Model\PresenterModel */
    $adminModel = $this->getModel('Skill', 'Administrator');
    $result = $adminModel->save($data);

    if ($result) {
      $app->enqueueMessage('Class submission save successful.');
    }
    return $result;
  }
}
