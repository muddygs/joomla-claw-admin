<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Language\Text;
use Exception;
use Joomla\CMS\Table\Table;
use UnexpectedValueException;
use Joomla\DI\Exception\KeyNotFoundException;

/**
 * Minimal code for a component to configure state, but do nothing else
 */
class RegistrationSurveyModel extends AdminModel
{
    public function getForm($data = [], $loadData = false)
    {    // Get the form.
        $form = $this->loadForm(
            'com_claw.registrationsurvey',
            'registrationsurvey',
            [
                'control' => 'jform',
                'load_data' => $loadData
            ]
        );

        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     * Table is loaded from the admin site (i.e., CLASSNAMESTable -- note plural version)
     * @param string $name 
     * @param string $prefix 
     * @param array $options 
     * @return Table 
     * @throws UnexpectedValueException 
     * @throws Exception 
     * @throws KeyNotFoundException 
     */
    public function getTable($name = '', $prefix = '', $options = array())
    {
      $name = 'RegistrationSurvey';
      $prefix = 'Table';
  
      if ($table = $this->_createTable($name, $prefix, $options)) {
        return $table;
      }
  
      throw new \Exception(Text::sprintf('JLIB_APPLICATION_ERROR_TABLE_NAME_NOT_SUPPORTED', $name), 0);
    }
  
}
