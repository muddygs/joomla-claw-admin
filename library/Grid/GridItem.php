<?php

namespace ClawCorpLib\Grid;

use Joomla\Database\DatabaseDriver;

\defined('_JEXEC') or die;

// This class enforces data format for received form data

// TODO: This can be improved with php8
class GridItem
{
  // TODO: Add validation
  public function __construct(
    public int $id,
    public string $grid_id,
    public string $time,
    public float $length,
    public string $title,
    public string $description,
    public string $event,
    public string $shift_area,
    public string $requirements,
    public array $coordinators,
    public int $published,
    public string $day,
    public int $needed,
    public int $event_id
  ) {
  }

  public function store(DatabaseDriver $db)
  {
    // Load the shift row to update

    // Update specific day values (quantity, eventbooking event id)

  }
}
