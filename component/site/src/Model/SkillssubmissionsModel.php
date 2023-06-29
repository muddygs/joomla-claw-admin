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

use Joomla\CMS\Factory;

use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Helpers\Skills;
use ClawCorpLib\Lib\ClawEvents;

use Exception;
use InvalidArgumentException;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\Model\FormModel;
use Joomla\Database\Exception\DatabaseNotFoundException;
use Joomla\Database\Exception\UnsupportedAdapterException;
use Joomla\Database\Exception\QueryTypeAlreadyDefinedException;
use RuntimeException;


/**
 * Methods to handle presenter bios and class listing for display.
 *
 * @since  1.6
 */
class SkillssubmissionsModel extends BaseDatabaseModel
{
  /**
   * @param string $event (Optional) Event alias
   * @return array|null 
   * @throws DatabaseNotFoundException 
   * @throws Exception 
   * @throws UnsupportedAdapterException 
   * @throws QueryTypeAlreadyDefinedException 
   * @throws RuntimeException 
   * @throws InvalidArgumentException 
   */
  public function GetPresenterBios(string $event = '')
  {
    $db = $this->getDatabase();
    $app = Factory::getApplication();
    $uid = $app->getIdentity()->id;
  
    return Skills::GetPresenterBios($db, $uid, $event);
  }

  public function GetPresenterClasses(string $event = '')
  {
    $db = $this->getDatabase();
    $app = Factory::getApplication();
    $uid = $app->getIdentity()->id;
  
    return Skills::GetPresenterClasses($db, $uid, $event);
  }

  public function GetEventInfo() : \ClawCorpLib\Lib\EventInfo
  {
    $events = new ClawEvents(Aliases::current);

    $info = $events->getClawEventInfo();
    return $info;
  }
}