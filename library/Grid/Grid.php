<?php

namespace ClawCorpLib\Grid;

use Joomla\Database\DatabaseDriver;

// This class enforces data format for received form data

// TODO: This can be improved with php8
class Grid
{
  public int $id;
  public int $shift_id;
  public int $row_id;
  public string $time;
  public int $length;
  public array $shifts;

  // TODO: Add validation
  public function __construct(array $formData)
  {
    $this->time = $formData['time'];
    $this->length = (int)$formData['length'];
    $this->id = $formData['id'];
    $this->row_id = $formData['row_id'] ?? 0;

    $this->shifts = [];
    foreach (['tue', 'wed', 'thu', 'fri', 'sat', 'sun', 'mon'] as $day) {
      
      $key = $day . 'pri';
      if (array_key_exists($key, $formData)) {
        $this->shifts[$key] = (int)$formData[$key];
      } else {
        $this->shifts[$key] = 0;
      }
    };
  }

  public function store(DatabaseDriver $db, int $sid)
  {
    $this->shift_id = $sid;

    if ($this->id === 0) {
      $result = $db->insertObject('#__claw_shifts_grids', $this, 'id');
    }
  }
}
