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

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use ClawCorpLib\Helpers\Skills;

/**
 * Methods to handle public class listing.
 */
class SkillsClassModel extends BaseDatabaseModel
{
  public function GetClass(int $cid, string $event): ?object
  {
    $db = $this->getDatabase();
    $skills = new Skills($db, $event);
    $class = $skills->GetClass($cid);

    return $class;
  }
}