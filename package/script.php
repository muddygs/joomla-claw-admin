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

    // On update/uninstall, this is the existing row in #__extensions
    $oldVersion = null;

    if ($adapter->currentExtensionId && $adapter->extension) {
      $oldVersion = (string) $adapter->extension->version;
    }

    echo "Update from $oldVersion to $newVersion";

    $this->newVersion = $newVersion;
    $this->oldVersion = $oldVersion;

    if (version_compare($this->oldVersion, '25.0.8', '<=')) {
      // Make sure event_capacity is in #__claw_packages
      /** @var \Joomla\Database\DatabaseDriver */
      $db = \Joomla\CMS\Factory::getContainer()->get('DatabaseDriver');
      $tables = $db->getTableList();

      if (in_array('#__claw_packages_deploy', $tables)) {
        $columns = $db->getTableColumns('#__claw_packages_deploy', false);

        if (!isset($columns['event_capacity'])) {
          $db->setQuery("ALTER TABLE `#__claw_packages_deploy` ADD `event_capacity` INT NOT NULL DEFAULT '0' AFTER `packageInfoType`;");
          $db->execute();
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
