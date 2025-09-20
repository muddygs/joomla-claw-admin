<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */


namespace ClawCorp\Component\Claw\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Input\Input;
use ClawCorpLib\Traits\Controller;

/**
 * Shifts list controller class.
 */
class EventcopyController extends FormController
{
  use Controller;

  public function __construct(
    $config = [],
    ?MVCFactoryInterface $factory = null,
    ?CMSApplication $app = null,
    ?Input $input = null,
    ?FormFactoryInterface $formFactory = null
  ) {
    parent::__construct($config, $factory, $app, $input, $formFactory);

    $this->controllerSetup();
  }

  public function doCopyEvent()
  {
    $this->checkToken();

    /** @var \ClawCorp\Component\Claw\Administrator\Model\EventcopyModel */
    $model = $this->model;

    // Extract individual values from the filtered data
    // Validation occurs in doCopyEvent
    $from = $this->data['from_event'] ?? '';
    $to = $this->data['to_event'] ?? '';

    $response = $model->doCopyEvent($from, $to, $this->data['tables'] ?? [], $this->data['delete'] ?? false);
    header('Content-Type: text/html');
    echo $response; // htmx -> #results
  }
}
