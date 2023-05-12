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

class DisplayController extends BaseController
{
  protected $app;

  // TODO: temp for debugging
  public function __construct(
    $config = [],
    MVCFactoryInterface $factory = null,
    ?CMSApplication $app = null,
    ?Input $input = null,
    FormFactoryInterface $formFactory = null
  ) {
    Helpers::sessionSet('formdata', '');
    Helpers::sessionSet('photo', '');

    /** @var \Joomla\CMS\Application\SiteApplication */
    $app = Factory::getApplication();
    $menu = $app->getMenu()->getActive();
    Helpers::sessionSet('menuid', $menu->id);

    // $view = $input->get('view');
    // $task = $input->get('task');
    // $id = $input->get('id');

    parent::__construct($config, $factory, $app, $input, $formFactory);
  }

  public function copy()
  {
    $this->app = Factory::getApplication();
    $input = $this->app->input;

    $view = $input->get('view');
    $task = $input->get('task');
    $id = $input->get('id');

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
        break;

      default:
        # code...
        break;
    }
  }
}
