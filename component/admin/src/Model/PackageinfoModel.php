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
  
    if ( !$loadData ) return $form;

    // Populate combo select list from any deployed events

    // Get the event alias from the form
    $eventAlias = $form->getField('eventAlias')->value;
    $packageInfoType = $form->getField('packageInfoType')->value;
    $eventPackageType = $form->getField('eventPackageType')->value;

    // meta can only be populated with known information (requires save before full edit)
    if ( !EventInfo::isValidEventAlias($eventAlias) ) return $form;

    // Validate other fields
    $packageInfoType = PackageInfoTypes::tryFrom($packageInfoType);
    if ( $packageInfoType == null ) return $form;

    $eventPackageType = EventPackageTypes::tryFrom($eventPackageType);
    if ( $eventPackageType == null ) return $form;

    $eventConfig = new EventConfig($eventAlias);

    /** @var \Joomla\CMS\Form\Field\ListField */
		$parentField = $form->getField('meta');

    if ( $packageInfoType == PackageInfoTypes::combomeal) {
      /** @var \ClawCorpLib\Lib\PackageInfo */
      foreach ( $eventConfig->packageInfos AS $p) {
        if (
          $p->eventId > 0 &&
          $p->published == EbPublishedState::published &&
          $p->packageInfoType == PackageInfoTypes::addon
        ) {
          $parentField->addOption($p->description, ['value' => $p->eventId]);
        }
      }
    } else if ( $eventPackageType == EventPackageTypes::vip ) {
      /** @var \ClawCorpLib\Lib\PackageInfo */
      foreach ( $eventConfig->packageInfos AS $p) {
        if (
          $p->eventId > 0 &&
          $p->published == EbPublishedState::published &&
          ( $p->eventPackageType == EventPackageTypes::combo_meal_1 ||
            $p->eventPackageType == EventPackageTypes::combo_meal_2 ||
            $p->eventPackageType == EventPackageTypes::combo_meal_3 ||
            $p->eventPackageType == EventPackageTypes::combo_meal_4 )
        ) {
          $parentField->addOption($p->description, ['value' => $p->eventId]);
        }
      }

      $form->setFieldAttribute('meta', 'multiple', false);
    }

    return $form;
  }

}