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
    $from = (string)($this->data['from_event'] ?? '');
    $to = (string)($this->data['to_event'] ?? '');

    $tables = $this->data['tables'] ?? [];
    if (!is_array($tables)) {
      $tables = [$tables];
    }
    $tables = array_values(array_unique(array_filter($tables, 'strlen')));

    $rawDelete = $this->data['delete'] ?? false;
    $delete = in_array($rawDelete, [1, '1', true, 'true', 'on'], true);

    try {
      $response = $model->doCopyEvent($from, $to, $tables, $delete);

      header('Content-Type: text/html; charset=UTF-8');
      echo $response; // htmx -> #results
    } catch (\Throwable $e) {
      header('Content-Type', 'text/html; charset=UTF-8', true);
      echo '<div class="text-danger">' . htmlspecialchars($e->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</div>';
    }

    $this->app->close();
  }
}
