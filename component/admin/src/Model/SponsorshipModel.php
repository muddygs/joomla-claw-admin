<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\Model;

use ClawCorpLib\Enums\EventPackageTypes;
use ClawCorpLib\Enums\PackageInfoTypes;
use Joomla\CMS\Factory;

defined('_JEXEC') or die;


/**
 * Extends EventconfigModel
 *
 */
class SponsorshipModel extends EventconfigModel
{
  public function save($data)
  {
    // Save the data in the session in case of error
    /** @var \Joomla\CMS\Application\AdministratorApplication */
    $app = Factory::getApplication();
    $app->setUserState('com_claw.edit.sponsorship.data', $data);

    // Convert meta to a single column array
    $data['meta'] = json_encode([]);
    $data['packageInfoType'] = PackageInfoTypes::sponsorship->value;
    $data['eventPackageType'] = EventPackageTypes::sponsorship->value;

    $result = parent::save($data);
    return $result;
  }

  public function getForm($data = array(), $loadData = true)
  {
    $form = $this->loadForm('com_claw.sponsorship', 'sponsorship', array('control' => 'jform', 'load_data' => $loadData));
    if (empty($form)) return false;

    return $form;
  }
}
