<?php

/**
 * @package     ClawCorpLib
 * @subpackage  com_claw
 *
 * @copyright   (C) 2025 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorpLib\Grid;

use ClawCorpLib\Iterators\GridShiftArray;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use ClawCorpLib\Traits\PackageDeploy;
use ClawCorpLib\Lib\EventInfo;

\defined('JPATH_PLATFORM') or die;

final class GridShifts
{
  use PackageDeploy;

  public GridShiftArray $shifts;
  private DatabaseDriver $db;

  public function __construct(
    public EventInfo $eventInfo,
    private bool $useDeployed = false,
  ) {
    $this->db = Factory::getContainer()->get('DatabaseDriver');
    $this->shifts = new GridShiftArray();
    $this->populateShifts();
  }

  protected function getDb(): DatabaseDriver
  {
    return $this->db;
  }

  private function populateShifts(): void
  {
    $eventAlias = $this->eventInfo->alias;
    $table = $this->useDeployed ? self::getDeployedTableName(GridShift::SHIFTS_TABLE) : GridShift::SHIFTS_TABLE;

    $query = $this->db->createQuery();
    $query->select('id')
      ->from($table)
      ->where('event = :event')
      ->bind(':event', $eventAlias)
      ->order('id');
    $this->db->setQuery($query);

    $gridIds = $this->db->loadColumn();

    foreach ($gridIds as $gid) {
      $gridShift = new GridShift($gid, $this->useDeployed);
      $this->shifts[$gid] = $gridShift;
    }
  }
}
