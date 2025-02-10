<?php

/**
 * @package     ClawCorpLib
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorpLib\Traits;

use Joomla\CMS\Factory;

trait Controller
{
  private $model;
  private $table;
  private $data;
  private $stateContext;
  private $cid;

  function controllerSetup(): void
  {
    $this->model   = $this->getModel();

    if ($this->model !== false) {
      try {
        $this->table   = $this->model->getTable();
      } catch (\Exception) {
        $this->table = null;
      }
    }

    $this->data    = $this->input->post->get('jform', [], 'array');

    if (property_exists($this, 'context')) {
      $this->stateContext = implode('.', [$this->option, 'edit', $this->context]);
    }

    $this->cid = $this->input->post->get('cid', [], 'array');

    $this->text_prefix = 'COM_CLAW';
  }

  function getModel($name = '', $prefix = '', $config = [])
  {
    // This is a bad hack. It happens to work because all the model names are of the
    // form Model(s), with the (s) is the only difference between list and edit views.
    $name = $this->name;

    if (str_ends_with($name, 's')) {
      $name = substr($name, 0, -1);
    }

    $name = ucfirst($name);
    $isAdmin = Factory::getApplication()->isClient('administrator');
    return parent::getModel($name, $isAdmin ? 'Administrator' : 'Site', ['ignore_request' => true]);
  }
}
