<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\Model;

use Joomla\CMS\Factory;
use ClawCorpLib\Enums\EventPackageTypes;
use ClawCorpLib\Enums\PackageInfoTypes;
use ClawCorpLib\Lib\ClawEvents;
use ClawCorpLib\Helpers\Helpers;

defined('_JEXEC') or die;

/**
 * Extends EventconfigModel
 */
class SpainfoModel extends EventconfigModel
{
  public function save($data)
  {
    // Save the data in the session in case of error
    /** @var \Joomla\CMS\Application\AdministratorApplication */
    $app = Factory::getApplication();
    $app->setUserState('com_claw.edit.spainfo.data', $data);

    // TODO: Put into config db table
    $data['category'] = ClawEvents::getCategoryId('spa');
    $data['packageInfoType'] = PackageInfoTypes::spa->value;
    $data['eventPackageType'] = EventPackageTypes::spa->value;
    $data['title'] = implode(' ', ['Spa Session', ucfirst($data['day']), Helpers::formatTime($data['start_time']), '(' . $data['length'] - 10 . ' minutes)']);
    $data['alias'] ??= 'TBD';

    $result = parent::save($data);
    return $result;
  }

  protected function loadFormData()
  {
    $data = parent::loadFormData();

    // Will only be a property upon initial load
    if (isset($data) && is_object($data) && property_exists($data, 'delta_time_minutes')) {
      $data->length = $data->delta_time_minutes + 10;
    }

    return $data;
  }

  public function getForm($data = array(), $loadData = true)
  {
    $form = $this->loadForm('com_claw.spainfo', 'spainfo', array('control' => 'jform', 'load_data' => $loadData));
    if (empty($form)) return false;

    if (!$loadData) return $form;

    if ($form->getField('length') && array_key_exists('length', $data)) {
      $form->setValue('length', null, $data['length']);
    }

    return $form;
  }
}
