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
use ClawCorpLib\Lib\ClawEvents;
use Joomla\CMS\Factory;

defined('_JEXEC') or die;

/**
 * Extends EventconfigModel
 *
 */
class SpeeddatinginfoModel extends EventconfigModel
{
  public function save($data)
  {
    // Save the data in the session in case of error
    /** @var \Joomla\CMS\Application\AdministratorApplication */
    $app = Factory::getApplication();
    $app->setUserState('com_claw.edit.speeddatinginfo.data', $data);

    $data['packageInfoType'] = PackageInfoTypes::speeddating->value;
    $data['eventPackageType'] = EventPackageTypes::speeddating->value;

    // TODO: Put into config db table
    $data['category'] = ClawEvents::getCategoryId('speed-dating');

    $result = parent::save($data);
    return $result;
  }

  public function getForm($data = array(), $loadData = true)
  {
    $form = $this->loadForm('com_claw.speeddatinginfo', 'speeddatinginfo', array('control' => 'jform', 'load_data' => $loadData));
    if (empty($form)) return false;

    return $form;
  }
}

