<?php

/**
 * @package     ClawCorpLib
 * @subpackage  com_claw
 *
 * @copyright   (C) 2025 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorpLib\Traits;

trait PackageDeploy
{
  function syncToDeployedTable(object $data, string $dstTable, string $key = 'id')
  {
    if (!property_exists($data, $key) || !is_int($data->$key) || $data->$key == 0) {
      throw new \Exception("Cannot sync record without valid $key id");
    }

    $this->db->transactionStart();

    try {
      // insertObject allows explicit primary key values
      $this->db->insertObject($dstTable, $data, $key);
      $this->db->transactionCommit();
    } catch (\RuntimeException $e) {
      // 1062 = duplicate key
      if ($e->getCode() == 1062) {
        //$this->db->clearErrors();
        $this->db->updateObject($dstTable, $data, $key);
        $this->db->transactionCommit();
      } else {
        $this->db->transactionRollback();
        throw $e;
      }
    }
  }
}
