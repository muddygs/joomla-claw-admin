<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;
use ClawCorpLib\Lib\EventInfo;

$button = '';

$published = match ($this->row->published) {
  0 => 'Unpublished',
  1 => 'Published',
  default => 'Pending Review'
};

if ($this->canSubmit) {
  if ($this->row->event == $this->currentEventInfo->alias) {
    $buttonRoute = Route::_('index.php?option=com_claw&view=skillsubmission&id=' . $this->row->id);
    $msg = 'View/Edit Class';
  } else {
    $buttonRoute = Route::_('index.php?option=com_claw&task=copyskill&id=' . $this->row->id);
    $msg = 'Resubmit for ' . $this->currentEventInfo->description;
  }

  $button = <<< HTML
HTML;
}

$eventInfo = new EventInfo(alias: $this->row->event, withUnpublished: true);

?>
<tr>
  <td><?= $eventInfo->description ?></td>
  <td><?= $this->row->title ?></td>
  <td><?= $published ?></td>
  <td>
    <a name="edit-class" id="edit-class-{$this->row->id}" class="btn btn-danger" href="<?= $buttonRoute ?>" role="button">
      <?= $msg ?>
    </a>
  </td>
</tr>
