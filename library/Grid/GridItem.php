<?php

/**
 * @package     ClawCorpLib
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorpLib\Grid;

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
  ) {}
}
