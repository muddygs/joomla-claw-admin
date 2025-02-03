<?php

use Joomla\Filesystem\Folder;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\Database\DatabaseDriver;

class pkg_clawInstallerScript
{
  /**
   * Constructor
   *
   * @param   InstallerAdapter  $adapter  The object responsible for running this script
   */
  public function __construct(InstallerAdapter $adapter) {}

  /**
   * Called before any type of action
   *
   * @param   string  $route  Which action is happening (install|uninstall|discover_install|update)
   * @param   InstallerAdapter  $adapter  The object responsible for running this script
   *
   * @return  boolean  True on success
   */
  public function preflight($route, InstallerAdapter $adapter)
  {
    return true;
  }

  /**
   * Called after any type of action
   *
   * @param   string  $route  Which action is happening (install|uninstall|discover_install|update)
   * @param   InstallerAdapter  $adapter  The object responsible for running this script
   *
   * @return  boolean  True on success
   */
  public function postflight($route, $adapter)
  {
    return true;
  }

  /**
   * Called on installation
   *
   * @param   InstallerAdapter  $adapter  The object responsible for running this script
   *
   * @return  boolean  True on success
   */
  public function update(InstallerAdapter $adapter)
  {
    $result = true;
    $result &= $this->copyLayouts();
    return $result;
  }

  private function copyLayouts()
  {
    $src = __DIR__ . '/layouts/claw/';
    $dest = JPATH_ROOT . '/layouts/claw/';

    if (!is_dir($dest)) {
      if (!Folder::create($dest)) {
        echo "Failed to create $dest.";
      }
    }

    $result = Folder::copy($src, $dest, '', true);
    if (!$result) {
      echo "Layouts copy failed.";
    }

    return $result;
  }

  private function removeLayouts()
  {
    $dest = JPATH_ROOT . '/layouts/claw/';

    if (is_dir($dest)) {
      return Folder::delete($dest);
    }

    return true;
  }

  /**
   * Called on update
   *
   * @param   InstallerAdapter  $adapter  The object responsible for running this script
   *
   * @return  boolean  True on success
   */
  public function install(InstallerAdapter $adapter)
  {
    return $this->update($adapter);
  }

  /**
   * Called on uninstallation
   *
   * @param   InstallerAdapter  $adapter  The object responsible for running this script
   */
  public function uninstall(InstallerAdapter $adapter)
  {
    $result = true;

    $result &= $this->removeLayouts();
    return $result;
  }

  /**
   * Execute queries from the given file
   *
   * @param   string  $file
   */
  public function executeSqlFile(DatabaseDriver $db, string $file)
  {
    $sql     = file_get_contents($file);
    $queries = $db->splitSql($sql);

    foreach ($queries as $query) {
      $query = trim($query);

      if ($query != '' && $query[0] != '#') {
        $db->setQuery($query)
          ->execute();
      }
    }
  }
}
