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

use ClawCorpLib\Helpers\Helpers;
use Joomla\CMS\Factory;

use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Helpers\Skills;
use ClawCorpLib\Lib\ClawEvents;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Methods to handle public class listing.
 */
class SkillsPresenterModel extends BaseDatabaseModel
{
  public function GetPresenter(int $uid, string $event = Aliases::current()): ?object
  {
    $db = $this->getDatabase();
    $presenter = Skills::GetPresenter($db, $uid, $event);

    return $presenter;
  }
}