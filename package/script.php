<?php

use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\Archive\Archive;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Installer;
use Joomla\Database\DatabaseDriver;

class pkg_clawInstallerScript
{
  /**
   * Constructor
   *
   * @param   InstallerAdapter  $adapter  The object responsible for running this script
   */
  public function __construct(InstallerAdapter $adapter)
  {
  }

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
    $status = true;

    if ( !in_array($route, ['install', 'update', 'discover_install']) ) {
      return true;
    }

    if ( in_array($route, ['install', 'update']) ) {
      $status = $this->extractTarball($adapter);
    }

    /*
    // Verify that base key/value pairs exist in #__claw_field_values
    $db = Factory::getContainer()->get('DatabaseDriver');
    $query = $db->getQuery(true);
    $query->select('COUNT(*)')
      ->from($db->qn('#__claw_field_values'));
    $db->setQuery($query);

    if (!$db->loadResult())
    {
      $sqlFile = JPATH_ADMINISTRATOR . '/components/com_claw/sql/config.fieldvalues.sql';
      $this->executeSqlFile($db, $sqlFile);
    }
     */

    return $status;
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
    return true;
  }

  private function extractTarball(): bool
  {
    $src = JPATH_LIBRARIES . '/claw/svn/j4_custom_code_current.tar.gz';
    $dest = JPATH_ROOT;
    $result = true;


    $archive = new Archive(['tmp_path' => JPATH_ROOT . '/tmp']);

    if (!Folder::exists($dest)) {
      Folder::create($dest);
    }

    try {
      $archive->extract($src, $dest);
    } catch (\Exception $e) {
      $result = false;
    }

    if ( !$result ) {
      echo '<p>Could not extract overlay tarball</p>';
    } else {
      echo '<p>Overlay tarball extracted</p>';
    }

    return $result;

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
    return true;
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
