<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\Model;

use ClawCorpLib\Enums\EventPackageTypes;
use ClawCorpLib\Enums\PackageInfoTypes;
use ClawCorpLib\Lib\EventInfo;
use Joomla\CMS\Factory;

defined('_JEXEC') or die;

/**
 * Extends EventconfigModel
 *
 */
class EquipmentrentalModel extends EventconfigModel
{
  public function save($data)
  {
    // Save the data in the session in case of error
    /** @var \Joomla\CMS\Application\AdministratorApplication */
    $app = Factory::getApplication();
    $app->setUserState('com_claw.edit.packageinfo.data', $data);

    // Do this now to validate the alias
    $this->eventInfo = new EventInfo($data['eventAlias']);
    $data['category'] = $this->eventInfo->eb_cat_equipment[0];

    $data['packageInfoType'] = PackageInfoTypes::equipment->value;
    $data['eventPackageType'] = EventPackageTypes::equipment->value;

    $result = parent::save($data);
    if ($result) $app->setUserState('com_claw.edit.packageinfo.data', null);
    return $result;
  }

  public function getForm($data = array(), $loadData = true)
  {
    $form = $this->loadForm('com_claw.equipmentrental', 'equipmentrental', array('control' => 'jform', 'load_data' => $loadData));
    if (empty($form)) return false;

    if (!$loadData) return $form;

    return $form;
  }
}

