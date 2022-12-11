<?php

namespace ClawCorpLib\Grids;

// This class enforces data format for received form data

use ClawCorp\Component\Claw\Administrator\Model\ShiftModel;
use Joomla\CMS\Factory;
use ClawCorpLib\Grid\Grid;
use Joomla\CMS\Form\Form;

class Grids extends ShiftModel
{
  private Grid $grid;
  private int $sid; // shift ID

  public function __construct(int $sid, array $formData)
  {
    $this->sid = $sid;
    $this->grid = new Grid($formData);  
  }

  public function store(): bool
  {
    if ( 0 == $this->grid->id )
    {
      $this->grid->id = $this->nextId();
    }

    return true;
  }

  public static function loadGrids(int $sid)
  {

  }

  public static function createEvents()
  {
    
  }

  private function nextId(): int
  {
    $db = Factory::getDbo();
    $query = 'SELECT max(row_id) FROM #__claw_shifts_grids WHERE shift_id='.$db->q($this->sid);
    $db->setQuery($query);
    $lastId = $db->loadResult();

    return !$lastId ? 1 : $lastId+1;
  }

  public function validate($form, $data, $group = null)
  {
    $this->setError("There was an error");
    return false;
  }

}
