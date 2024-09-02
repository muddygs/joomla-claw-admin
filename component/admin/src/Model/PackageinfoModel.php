<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\Model;

use ClawCorpLib\Enums\EbPublishedState;
use ClawCorpLib\Enums\EventPackageTypes;
use ClawCorpLib\Enums\PackageInfoTypes;
use ClawCorpLib\Lib\EventConfig;
use ClawCorpLib\Lib\EventInfo;
#use ClawCorpLib\Helpers\Config;

defined('_JEXEC') or die;


/**
 * Extends EventconfigModel
 *
 */
class PackageinfoModel extends EventconfigModel
{
  public function save($data)
  {
    return parent::save($data);
  }

  public function getForm($data = array(), $loadData = true)
  {
    $form = $this->loadForm('com_claw.packageinfo', 'packageinfo', array('control' => 'jform', 'load_data' => $loadData));
    if (empty($form)) return false;

    if (!$loadData) return $form;


    // Populate combo select list from any deployed events

    // Get the event alias from the form
    $eventAlias = $form->getField('eventAlias')->value;
    $packageInfoType = $form->getField('packageInfoType')->value;
    $eventPackageType = $form->getField('eventPackageType')->value;

    // meta can only be populated with known information (requires save before full edit)
    if (!EventInfo::isValidEventAlias($eventAlias)) return $form;
    #$config = new Config($eventAlias);
    #$defaultGroupId = (int)$config->getGlobalConfig('packaginfo_registered_group');

    #if (is_null($defaultGroupId)) {
    #throw new \Exception('com_claw requires group IDs for Public and Registered.');
    #}

    // Validate other fields
    $packageInfoType = PackageInfoTypes::tryFrom($packageInfoType);
    if ($packageInfoType == null) return $form;

    $eventPackageType = EventPackageTypes::tryFrom($eventPackageType);
    if ($eventPackageType == null) return $form;

    /** @var \Joomla\CMS\Form\Field\UserGroupListField */
    #$parentField = $form->getField('group_id');
    #var_dump($parentField);
    #$xmlnode = $parentField->element;
    #$dnode = dom_import_simplexml($parentField->element);
    #$dnode->setAttribute('default', $defaultGroupId);

    #dd($parentField);

    /** @var \Joomla\CMS\Form\Field\ListField */
    $parentField = $form->getField('meta');

    // These statements correspond to the packageinfo.xml form layout field for "meta"

    $staffSpecial = [
      EventPackageTypes::claw_staff,
      EventPackageTypes::claw_board,
      EventPackageTypes::vip,
    ];

    // Select addon
    if (
      $packageInfoType == PackageInfoTypes::combomeal ||
      in_array($eventPackageType, $staffSpecial)
    ) {
      $eventConfig = new EventConfig($eventAlias);
      /** @var \ClawCorpLib\Lib\PackageInfo */
      foreach ($eventConfig->packageInfos as $p) {
        if (
          $p->eventId > 0 &&
          $p->published == EbPublishedState::published &&
          $p->packageInfoType == PackageInfoTypes::addon
        ) {
          $parentField->addOption($p->title, ['value' => $p->eventId]);
        }
      }
    }

    return $form;
  }
}
