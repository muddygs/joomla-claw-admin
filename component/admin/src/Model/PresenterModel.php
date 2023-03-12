<?php
/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2022 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Image\Image;

use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Lib\Aliases;

/**
 * Methods to handle a list of records.
 *
 * @since  1.6
 */
class PresenterModel extends AdminModel
{
  	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $text_prefix = 'COM_CLAW_PRESENTER';

	public function delete(&$pks)
	{
		// TODO: Delete presenter image files
		parent::delete($pks);
	}

	public function save($data)
	{
		//TODO: Check that record does not already exist (if new)
		//$this->setError('nope');
		//return false;

		$data['mtime'] = Helpers::mtime();
		if ( !$data['submission_date'] ) $data['submission_date'] = date("Y-m-d");

		if ( array_key_exists('arrival', $data)) $data['arrival'] = implode(',',$data['arrival']);

		$input = Factory::getApplication()->input;
		$files = $input->files->get('jform');
		$tmp_name = $files['photo_upload']['tmp_name'];
		$mime = $files['photo_upload']['type'];
		$error = $files['photo_upload']['error'];
	
		if ( 0 == $error ) {
			$upload = implode(DIRECTORY_SEPARATOR, ['..', Aliases::presentersdir, 'orig', $data['uid']]);
			switch ($mime) {
				case 'image/jpeg':
					$upload .= '.jpg';
					break;
				
				default:
					$upload .= '.png';
					break;
			}

			if ( File::upload($tmp_name, $upload))
			{
				$output = implode(DIRECTORY_SEPARATOR, ['..', Aliases::presentersdir, 'web', $data['uid'].'.jpg']);
				$image = new Image();
				$image->loadFile($upload);
				$image->resize(300, 300, false);
				$image->toFile($output, IMAGETYPE_JPEG, ['quality' => 80]);

				// Success
			} else {
				// Failure
			}
		}

		return parent::save($data);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  Form|boolean  A Form object on success, false on failure
	 *
	 * @since   1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_claw.presenter', 'presenter', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since   1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$app = Factory::getApplication();
		$data = $app->getUserState('com_claw.edit.presenter.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $name     The table name. Optional.
	 * @param   string  $prefix   The class prefix. Optional.
	 * @param   array   $options  Configuration array for model. Optional.
	 *
	 * @return  Table  A Table object
	 *
	 * @since   3.0
	 * @throws  \Exception
	 */
	public function getTable($name = '', $prefix = '', $options = array())
	{
		$name = 'Presenters';
		$prefix = 'Table';

		if ($table = $this->_createTable($name, $prefix, $options))
		{
			return $table;
		}

		throw new \Exception(Text::sprintf('JLIB_APPLICATION_ERROR_TABLE_NAME_NOT_SUPPORTED', $name), 0);
	}
}