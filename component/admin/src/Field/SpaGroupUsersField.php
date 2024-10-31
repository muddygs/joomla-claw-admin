<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\Field;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Factory;
use Joomla\CMS\User\UserFactoryInterface;

use ClawCorpLib\Helpers\Config;

defined('_JEXEC') or die;

class SpaGroupUsersField extends UsersByGroupField
{
  public $type = 'SpaGroupUsers';

  protected $groupids;

  public function setup(\SimpleXMLElement $element, $value, $group = null)
  {
    $result = parent::setup($element, $value, $group);

    if ($result == true) {
      $spaGroupId = Config::getGlobalConfig('packageinfo_spa_group', 0);

      if (0 == $spaGroupId) {
        throw (new \Exception('Spa Group not set'));
      }

      $this->groupids  = [$spaGroupId];
    }

    return $result;
  }

  /**
   * Customized method to populate the field option groups.
   *
   * @return  array  The field option objects as a nested array in groups.
   */
  protected function getOptions()
  {
    $this->listItems = parent::getOptions();

    /** @var \Joomla\CMS\User\UserFactoryInterface */
    $userfactory = Factory::getContainer()->get(UserFactoryInterface::class);

    $currentValue = $this->__get('value');

    foreach ($this->groupids as $id) {
      $userIds = Access::getUsersByGroup($id);

      foreach ($userIds as $id) {
        /** @var \Joomla\CMS\User\User */
        $u = $userfactory->loadUserById($id);

        $this->listItems[] = (object)[
          'value'    => $u->id,
          'text'     => $u->name,
          'disable'  => false,
          'class'    => '',
          'selected' => $u->id == $currentValue ? true : false,
          'checked'  => $u->id == $currentValue ? true : false,
          'onclick'  => '',
          'onchange' => ''
        ];
      }
    }

    return $this->listItems;
  }
}
