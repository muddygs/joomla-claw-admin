<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use ClawCorpLib\Enums\SkillPublishedState;
use Joomla\CMS\Router\Route;
use ClawCorpLib\Lib\EventInfo;

/** @var \ClawCorpLib\Skills\Skill */
$class = $this->row;

$published = match ($class->published) {
  SkillPublishedState::published => 'Published',
  default => 'Pending Review'
};

$buttonRoute = '#';
$msg = '';

if ($this->userState->submissionsOpen) {
  if ($this->row->event == $this->currentEventInfo->alias) {
    $buttonRoute = Route::_('index.php?option=com_claw&view=skillsubmission&id=' . $this->row->id);
    $msg = 'View/Edit Class';
  } else {
    $buttonRoute = Route::_('index.php?option=com_claw&task=skillsubmission.copyskill&id=' . $this->row->id);
    $msg = 'Resubmit for ' . $this->currentEventInfo->description;
  }
}

$eventInfo = new EventInfo(alias: $class->event, withUnpublished: true);

?>
<tr>
  <td><?= $eventInfo->description ?></td>
  <td><?= $class->title ?></td>
  <td><?= $published ?></td>
  <td>
    <?php if ('' == $msg): ?>
      &nbsp;
    <?php else: ?>
      <a name="edit-class" id="edit-class-{$class->id}" class="btn btn-danger" href="<?= $buttonRoute ?>" role="button">
        <?= $msg ?>
      </a>
    <?php endif; ?>
  </td>
</tr>
