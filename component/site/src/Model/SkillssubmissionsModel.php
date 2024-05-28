<?php
/**
 * @package     ClawCorp.Component.SkillssubmissionModel
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
use ClawCorpLib\Lib\EventInfo;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\DatabaseInterface;

/**
 * Methods to handle presenter bios and class listing for display.
 *
 * @since  1.6
 */
class SkillssubmissionsModel extends BaseDatabaseModel
{
  private int $testUid = 0;

  /**
   * Retrieve the presenter bio for a given event.
   * @param EventInfo $eventInfo
   * @return object|null Bio object or null if no bio is found.
   */
  public function GetPresenterBio(EventInfo $eventInfo): ?object
  {
    $db = $this->getDatabase();
    $app = Factory::getApplication();
    $pid = !$this->testUid ? $app->getIdentity()->id : $this->testUid;
    $skills = new Skills($db, $eventInfo->alias);
    $bios = $skills->GetPresenterBios($pid);
    return is_null($bios) || !count($bios) ? null : $bios[0];
  }

  /**
   * Retrieve the list of classes the presenter is teaching for a given event.
   * @param EventInfo $eventInfo
   * @return array|null Array of class objects or null if no classes are found.
   */
  public function GetPresenterClasses(EventInfo $eventInfo): ?array
  {
    $db = $this->getDatabase();
    $app = Factory::getApplication();
    $pid = !$this->testUid ? $app->getIdentity()->id : $this->testUid;
    $skills = new Skills($db, $eventInfo->alias);
    return $skills->GetPresenterClasses($pid);
  }

  public function GetEventInfo() : \ClawCorpLib\Lib\EventInfo
  {
    return new EventInfo(Aliases::current(true));
  }

  public function getDatabase(): DatabaseInterface
  {
    return parent::getDatabase();
  }
}