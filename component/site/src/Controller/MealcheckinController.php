<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2025 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Site\Controller;

\defined('_JEXEC') or die;

use ClawCorpLib\Lib\Jwtwrapper;
use Joomla\CMS\MVC\Controller\BaseController;
use ClawCorpLib\Traits\Controller;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Input\Input;

class MealcheckinController extends BaseController
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

  public function checkin()
  {
    $input = $this->input;
    $token = $input->get('token', '');
    $search = $input->get('badgecode', '');
    $meal = $input->get('mealEvent', '', 'string');

    if (!Jwtwrapper::valid(page: 'meals-checkin', token: $token)) {
      echo 'invalid token';
      return;
    }

    /** @var \ClawCorp\Component\Claw\Site\View\Mealcheckin\HtmlView */
    $view = $this->getView('Mealcheckin', 'HtmxRecord');
    $view->search = $search;
    $view->mealPackageInfoId = $meal;

    $view->display();
  }
}
