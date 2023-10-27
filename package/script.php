<?php

use Joomla\Archive\Archive;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Installer;
use Joomla\Database\DatabaseDriver;

class com_clawInstallerScript
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
		/*
		if ( !in_array($route, ['install', 'update', 'discover_install']) ) {
			return true;
		}

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
	
		return true;
	}
	
	/**
	 * Called on installation
	 *
	 * @param   InstallerAdapter  $adapter  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function install(InstallerAdapter $adapter)
	{
			// Extract tarball here
			$src = JPATH_LIBRARIES . '/svn/j4_custom_code_current.tar.gz';
			$dest = JPATH_ROOT;

			$this->extractTarball($src, $dest);

			return true;
	}

	private function extractTarball($src, $dest)
	{
			$archive = new Archive;
			$archive->extract($src, $dest);
	}
	
	/**
	 * Called on update
	 *
	 * @param   InstallerAdapter  $adapter  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function update(InstallerAdapter $adapter)
	{
		return true;
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

		foreach ($queries as $query)
		{
			$query = trim($query);

			if ($query != '' && $query[0] != '#')
			{
				$db->setQuery($query)
					->execute();
			}
		}
	}
}
