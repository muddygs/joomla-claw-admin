<?php

/**
 * @package     ClawCorpLib
 * @subpackage  com_claw
 *
 * @copyright   (C) 2025 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorpLib\Traits;

use Joomla\Database\DatabaseDriver;

trait PackageDeploy
{
  abstract protected function getDb(): DatabaseDriver;

  public function syncToDeployedTable(object $data, string $baseTable, string $key = 'id'): void
  {
    if (!property_exists($data, $key) || !is_int($data->$key) || $data->$key === 0) {
      throw new \InvalidArgumentException("Cannot sync record without valid $key id");
    }

    $db = $this->getDb();
    $dstTable = $this->ensureDeployedTable($baseTable);

    $db->transactionStart();
    try {
      $db->insertObject($dstTable, $data, $key);
      $db->transactionCommit();
    } catch (\RuntimeException $e) {
      if ($e->getCode() === 1062) {                 // duplicate key
        $db->transactionRollback();
        $db->transactionStart();
        $db->updateObject($dstTable, $data, $key);
        $db->transactionCommit();
      } else {
        $db->transactionRollback();
        throw $e;
      }
    }
  }

  // Use this for consistency
  protected static function getDeployedTableName(string $baseTableName): string
  {
    return $baseTableName . '_deployed';
  }

  // baseTable should be like '#__packages'
  protected function ensureDeployedTable(string $baseTable): string
  {
    $db = $this->getDb();

    $dstTable  = $this->getDeployedTableName($baseTable);

    // does the deployed table already exist?
    // Assuming MariaDB: SHOW TABLES LIKE 'name'
    $exists = (bool) $db->setQuery(
      'SHOW TABLES LIKE ' . $db->quote($dstTable)
    )->loadResult();

    if (!$exists) {
      try {
        $sql = sprintf(
          'CREATE TABLE %s LIKE %s',
          $db->quoteName($dstTable),
          $db->quoteName($baseTable)
        );
        $db->setQuery($sql)->execute();
      } catch (\RuntimeException $e) {
        // Ignore "table exists" -- somehow we succeeded -- otherwise, rethrow
        if ((int) $e->getCode() !== 1050) {
          throw $e;
        }
      }
    }

    return $dstTable;
  }
}
