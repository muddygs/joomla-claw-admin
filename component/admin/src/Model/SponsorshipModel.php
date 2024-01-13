<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\Model;

use ClawCorpLib\Enums\EbPublishedState;
use ClawCorpLib\Enums\EventPackageTypes;
use ClawCorpLib\Enums\PackageInfoTypes;
use ClawCorpLib\Lib\EventConfig;
use ClawCorpLib\Lib\EventInfo;
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
    if ($result) $app->setUserState('com_claw.edit.sponsorship.data', null);
    return $result;
  }

  public function getForm($data = array(), $loadData = true)
  {
    $form = $this->loadForm('com_claw.sponsorship', 'sponsorship', array('control' => 'jform', 'load_data' => $loadData));
    if (empty($form)) return false;

    if (!$loadData) return $form;

    // Get the event alias from the form
    $eventAlias = $form->getField('eventAlias')->value;

    // meta can only be populated with known information (requires save before full edit)
    if (!EventInfo::isValidEventAlias($eventAlias)) return $form;

    return $form;
  }
}
