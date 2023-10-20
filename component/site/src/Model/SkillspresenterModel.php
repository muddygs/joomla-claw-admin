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
class SkillsPresenterModel extends BaseDatabaseModel
{
  public function GetPresenter(int $uid, string $event): ?object
  {
    $db = $this->getDatabase();
    $presenter = Skills::GetPresenter($db, $uid, $event);

    return $presenter;
  }
}