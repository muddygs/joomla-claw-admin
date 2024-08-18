<?php

namespace ClawCorpLib\Traits;

use Joomla\CMS\Language\Text;

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
    // $this->model   = parent::getModel(config: ['ignore_request' => true]);

    try {
      $this->table   = $this->model->getTable();
    } catch (\Exception) {
      $this->table = null;
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

    if ( str_ends_with($name, 's') ) {
      $name = substr($name, 0, -1);
    }

    $name = ucfirst($name);
    return parent::getModel($name, 'Administrator', ['ignore_request' => true]);
    return $this->model;
  }
}
