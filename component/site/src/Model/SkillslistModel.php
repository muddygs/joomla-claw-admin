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
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Methods to handle public class listing.
 */
class SkillslistModel extends BaseDatabaseModel
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
  public function GetPresenterList(string $event = Aliases::current)
  {
    $db = $this->getDatabase();
    return Skills::GetPresenterList($db, $event);
  }

  public function GetClassList(string $event = Aliases::current)
  {
    $db = $this->getDatabase();
    return Skills::GetClassList($db, $event);
  }

  public function GetEventInfo(string $alias = Aliases::current) : \ClawCorpLib\Lib\EventInfo
  {
    $events = new ClawEvents(clawEventAlias: $alias);

    $info = $events->getClawEventInfo();
    return $info;
  }
}