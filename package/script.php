<?php

use Joomla\Filesystem\Folder;
use Joomla\Archive\Archive;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerScriptInterface;
use Joomla\CMS\Language\Text;

return new class() implements InstallerScriptInterface {

  private string $minimumJoomla = '5.0.0';
  private string $minimumPhp    = '8.1.0';
  private ?string $oldVersion;
  private ?string $newVersion;

  public function install(InstallerAdapter $adapter): bool
  {
    return true;
  }

  public function update(InstallerAdapter $adapter): bool
  {
    $manifest   = $adapter->getManifest();
    $newVersion = (string) ($manifest->version ?? '');

    if ($adapter->extension) {
      // $adapter->extension is a Table\Extension row (#__extensions)
      $manifestCacheJson = $adapter->extension->manifest_cache ?? '';

      if ($manifestCacheJson !== '') {
        $manifestCache = json_decode($manifestCacheJson, true);

        if (is_array($manifestCache) && isset($manifestCache['version'])) {
          $oldVersion = (string) $manifestCache['version'];
        }
      }
    }

    echo "Update from $oldVersion to $newVersion";

    $this->newVersion = $newVersion;
    $this->oldVersion = $oldVersion;

    // this is a middle-ground for test/deploy. 
    if (version_compare($this->oldVersion, '26.0.8', '<=')) {
      echo "<br/>Validating #__claw_packages";

      // Make sure event_capacity is in #__claw_packages
      /** @var \Joomla\Database\DatabaseDriver */
      $db = \Joomla\CMS\Factory::getContainer()->get('DatabaseDriver');
      $tables = $db->getTableList();

      $realName = $db->replacePrefix('#__claw_packages_deployed');
      if (in_array($realName, $tables)) {
        $columns = $db->getTableColumns('#__claw_packages_deployed', false);

        if (!isset($columns['event_capacity'])) {
          $db->setQuery("ALTER TABLE `#__claw_packages_deployed` ADD `event_capacity` INT NOT NULL DEFAULT '0' AFTER `packageInfoType`;");
          $db->execute();
          echo "<br/>$realName updated";
        }
      }

      $realName = $db->replacePrefix('#__claw_packages');
      if (in_array($realName, $tables)) {
        $columns = $db->getTableColumns('#__claw_packages', false);

        if (!isset($columns['event_capacity'])) {
          $db->setQuery("ALTER TABLE `#__claw_packages` ADD `event_capacity` INT NOT NULL DEFAULT '0' AFTER `packageInfoType`;");
          $db->execute();
          echo "<br/>$realName updated";
        }
      }
    }

    return true;
  }

  public function uninstall(InstallerAdapter $adapter): bool
  {
    return true;
  }

  public function preflight(string $type, InstallerAdapter $adapter): bool
  {
    if (version_compare(PHP_VERSION, $this->minimumPhp, '<')) {
      Factory::getApplication()->enqueueMessage(sprintf(Text::_('JLIB_INSTALLER_MINIMUM_PHP'), $this->minimumPhp), 'error');
      return false;
    }

    if (version_compare(JVERSION, $this->minimumJoomla, '<')) {
      Factory::getApplication()->enqueueMessage(sprintf(Text::_('JLIB_INSTALLER_MINIMUM_JOOMLA'), $this->minimumJoomla), 'error');
      return false;
    }

    return true;
  }

  public function postflight(string $type, InstallerAdapter $adapter): bool
  {
    $result = true;

    if (in_array($type, ['install', 'update'])) {
      $result &= $this->extractTarball($adapter);
    }

    echo $result ? "CLAW Install Complete" : "CLAW Install Failed";
    return $result;
  }

  private function extractTarball(): bool
  {
    $src = JPATH_LIBRARIES . '/claw/svn/claw_custom_code_current.tar.gz';
    $dest = JPATH_ROOT;
    $result = true;

    $archive = new Archive(['tmp_path' => JPATH_ROOT . '/tmp']);

    if (!is_dir($dest)) {
      Folder::create($dest);
    }

    try {
      $archive->extract($src, $dest);
    } catch (\Exception) {
      $result = false;
    }

    if (!$result) {
      echo '<p>Could not extract overlay tarball.</p>';
    } else {
      echo '<p>Overlay tarball extraction successful.</p>';
    }

    return $result;
  }
};
