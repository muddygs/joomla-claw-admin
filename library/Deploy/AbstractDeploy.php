<?php

/**
 * @package     ClawCorpLib
 * @subpackage  com_claw
 *
 * @copyright   (C) 2025 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorpLib\Deploy;

use ClawCorpLib\Enums\EbPublishedState;
use ClawCorpLib\Helpers\Config;
use ClawCorpLib\Iterators\PackageInfoArray;
use ClawCorpLib\Lib\PackageInfo;
use ClawCorpLib\Lib\PackageInfos;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use ClawCorpLib\Lib\Ebmgmt;
use ClawCorpLib\Lib\EventInfo;

abstract class AbstractDeploy
{
  protected int $public_acl = 0;
  protected int $registered_acl = 0;
  protected DatabaseDriver $db;
  protected EventInfo $eventInfo;
  protected Date $registration_start_date;
  protected array $aliases = [];
  protected ?PackageInfos $livePackageInfos;
  protected ?PackageInfos $deployedPackageInfos;
  protected PackageInfoArray $deletedPackageInfoArray;
  protected bool $deployedNotInitialized = false;
  private array $log = [];

  public function __construct(
    public string $eventAlias,
  ) {
    try {
      $this->eventInfo = new EventInfo($eventAlias);
    } catch (\Exception) {
      throw new \InvalidArgumentException('Invalid for deployment - Event alias: ' . $this->eventAlias);
    }

    $this->registration_start_date = Factory::getDate('now', $this->eventInfo->timezone);

    $this->setDefaultAcls();
    /** @var \Joomla\Database\DatabaseDriver */
    $this->db = Factory::getContainer()->get('DatabaseDriver');

    date_default_timezone_set('Etc/UTC');

    $this->deletedPackageInfoArray = new PackageInfoArray();
  }

  /**
   * Formats an array into an alias for a given deployment type
   * This mess is here because the formatting has been inconsistent, and this at least
   * permits rapid review
   * @var array Alias components
   * @return string
   */
  abstract protected function formatAlias(array $in): string;

  /**
   * Load live and deployed packageinfos based on deployment package type scenario/filter
   */
  abstract protected function loadPackageInfos(): void;

  /**
   * Determine what package rows should be affected
   * Rows that are processed via packageInfo->save() get dropped before afterDeploy()
   * Deleted rows ($this->deletedPackageInfos) should be set here
   */
  protected function beforeDeploy(): bool
  {
    if (!count($this->deployedPackageInfos->packageInfoArray)) {
      $this->Log("Initializing deployed data");
      $this->deployedNotInitialized = true;
      return true;
    }

    $liveKeys = $this->livePackageInfos->packageInfoArray->keys();
    $deployedKeys = $this->deployedPackageInfos->packageInfoArray->keys();

    $commonKeys = array_unique(array_merge($liveKeys, $deployedKeys), SORT_NUMERIC);
    $deletedKeys = array_diff($deployedKeys, $liveKeys);

    // Only process differences
    foreach ($commonKeys as $packageId) {
      $deployedHash = null;
      $liveHash = null;

      if (isset($this->deployedPackageInfos->packageInfoArray[$packageId]))
        $deployedHash = $this->deployedPackageInfos->packageInfoArray[$packageId]->md5hash();
      if (isset($this->livePackageInfos->packageInfoArray[$packageId]))
        $liveHash = $this->livePackageInfos->packageInfoArray[$packageId]->md5hash();

      if (!is_null($liveHash) && !is_null($deployedHash)) {
        if ($deployedHash == $liveHash) {
          unset($this->livePackageInfos->packageInfoArray[$packageId]);
        }
      } else {
        if (!is_null($deployedHash))
          $this->deletedPackageInfoArray[$packageId] = $this->deployedPackageInfos->packageInfoArray[$packageId];
      }
    }

    // collect packages that will be unpublished in event booking
    foreach ($deletedKeys as $packageId) {
      $this->deletedPackageInfoArray[$packageId] = $this->deployedPackageInfos->packageInfoArray[$packageId];
    }

    return true;
  }

  /**
   * Handle deleted rows, push changes to deployed table
   */
  protected function afterDeploy(): bool
  {
    // All items updated via save call on packages during deploy
    if ($this->deployedNotInitialized) return true;

    /** @var \ClawCorpLib\Lib\PackageInfo $packageInfo */
    foreach ($this->deletedPackageInfoArray as $packageId => $packageInfo) {
      $this->unpublishEventBooking($packageInfo);
      $this->Log("Unpublished: $packageId -> {$packageInfo->eventId}");
      $this->deployedPackageInfos->packageInfoArray[$packageId]->DeleteDeployedPackage($packageId);
    }

    return true;
  }

  /**
   * Main routine for deployment; probably should loop on livePackageInfos
   */
  abstract protected function inDeploy(): bool;

  /**
   * Run the deploy, wrapped in a db transaction wit rollback upon error
   */
  public function deploy(): string
  {
    $this->loadPackageInfos();

    $this->db->transactionStart();

    try {
      $result = $this->beforeDeploy() && $this->inDeploy() && $this->afterDeploy();

      if ($result) {
        $this->db->transactionCommit();
        $this->Log("Database Changes Committed", 'text-success');
      } else {
        $this->db->transactionRollback();
        $this->Log("Database Changes Reverted Due to Error", "text-danger");
      }
    } catch (\Exception $e) {
      $this->db->transactionRollback();
      throw $e;
    }

    return $this->FormatLog();
  }

  protected function SyncEvent(
    EbSyncItem $item,
  ): EbSyncResponse {
    $sync = new EbSync($this->eventInfo, $item);
    return $sync->upsert($item);
  }

  protected function ValidateAliases(): bool
  {
    if (count($this->aliases) != count(array_unique($this->aliases))) {
      $this->Log("You have duplicate event titles that would conflict; correct event titles before deploying.", 'text-danger');
      $this->Log('<pre>' . print_r(array_count_values($this->aliases), true) . '</pre>');
      return false;
    }

    return true;
  }

  // Standard ID
  protected function HandleResponseStandard(EbSyncResponse $response, PackageInfo $packageInfo): int
  {
    $count = 0;

    if ($response->action == 'noop') {
      $this->Log("No changes: $packageInfo->title");
      if ($packageInfo->eventId) {
        $packageInfo->save(false);
      } else {
        $this->Log("Syncing");
        $packageInfo->SyncToDeployed(); // normally called at end of save()
      }
    } elseif ($response->action == 'update') {
      $packageInfo->eventId = $response->id;
      $this->Log("Updated: $packageInfo->title at event id $response->id");
      $packageInfo->save();
    } else {
      $count++;
      $this->Log("Added: $packageInfo->title at event id $response->id");
      $packageInfo->eventId = $response->id;
      $packageInfo->save();
    }

    return $count;
  }

  // Meta (JSON) ID Info
  protected function HandleResponseMeta(EbSyncResponse $response, PackageInfo $packageInfo, string $metaKey): int
  {
    $count = 0;

    if ($response->action == 'noop') {
      $this->Log("No changes: $packageInfo->title");
      if ($packageInfo->eventId) {
        $packageInfo->save(false);
      } else {
        $this->Log("Syncing");
        $packageInfo->SyncToDeployed(); // normally called at end of save()
      }
    } elseif ($response->action == 'update') {
      $packageInfo->meta->$metaKey->eventId = $response->id;
      $this->Log("Updated: $packageInfo->title to event id $response->id");
      $packageInfo->save();
    } else {
      $count++;
      $this->Log("Added: $packageInfo->title at event id $response->id");
      $packageInfo->meta->$metaKey->eventId = $response->id;
      $packageInfo->save();
    }

    return $count;
  }

  protected function unpublishEventBooking(PackageInfo $packageInfo)
  {
    if ($packageInfo->eventId == 0) return;

    $update = new Ebmgmt(
      eventInfo: $this->eventInfo,
      mainCategoryId: $packageInfo->category,
      itemAlias: $packageInfo->alias,
      title: $packageInfo->title,
      description: $packageInfo->description,
    );

    try {
      $update->load($packageInfo->eventId);
      $update->set('published', EbPublishedState::any->value);
      $update->update();
      $this->Log("Unpublished event id $packageInfo->eventId");
    } catch (\Exception) {
      $this->Log("Failed to unpublished event id $packageInfo->eventId");
    }
  }

  /**
   * Sets internal variables for public and registered groups 
   * @return void  */
  private function setDefaultAcls()
  {
    $this->public_acl = Config::getGlobalConfig('packageinfo_public_acl', 0);
    $this->registered_acl = Config::getGlobalConfig('packageinfo_registered_acl', 0);

    if (0 == $this->public_acl || 0 == $this->registered_acl) {
      throw new \Exception('Invalid ACL id');
    }
  }

  // TODO: format as some sort of table that makes sense, probably collect more than msg
  public function Log(string $msg, string $class = "")
  {
    if ($class) $msg = '<span class="' . $class . '">' . $msg . '</span>';
    $this->log[] = $msg;
  }

  public function FormatLog(): string
  {
    return '<p>' . implode('</p><p>', $this->log) . '</p>';
  }
}
