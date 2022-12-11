<?php

namespace ClawCorpLib\Grid;

// This class enforces data format for received form data

// TODO: This can be improved with php8
class Grid
{
  public int $id;
  public string $time;
  public int $length;
  public object $shifts;

  // TODO: Add validation
  public function __construct(array $formData)
  {
    $this->time = $formData['time'];
    $this->length = (int)$formData['length'];
    $this->id = $formData['id'];

    $this->shifts = (object)[];
    foreach ( ['tue','wed','thu','fri','sat','sun','mon'] AS $day )
    {
      $key = $day.'pri';
      if ( array_key_exists($key, $formData) )
      {
        $this->shifts->$key = (int)$formData[$key];
      }
      else
      {
        $this->shifts->$key = 0;
      }
      
    };

  }
}
