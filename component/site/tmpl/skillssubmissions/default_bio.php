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

$addBioButton = true;

/** @var \ClawCorpLIb\Skills\UserState */
$userState = $this->userState;

// Handle easy case where recent bio is not on file
if (is_null($userState->presenter)) {
  if ($userState->submissionsOpen):
?>
    <h3 class="text-primary text-center border border-danger p-3">
      Submissions are open for <?= $this->currentEventInfo->description ?>.
      You may add and edit your biography.
    </h3>
    <?php
  else:
    if ($userState->submissionsBioOnly):
    ?>
      <h3 class="text-warning text-center border border-danger p-3">
        Submissions are closed, but you may submit a biography (typically used for late entry).
        After submission, you will no longer be able to edit it.
      </h3>
    <?php
    else:
      $addBioButton = false;
    ?>
      <h3 class="text-warning text-center border border-danger p-3">
        Biography submissions are currently closed.
      </h3>
    <?php
    endif;
  endif;

  if ($addBioButton) {
    $buttonRoute = Route::_('index.php?option=com_claw&view=presentersubmission&id=0');
    ?>
    <a name="add-biography" id="add-biography" class="btn btn-danger" href="<?= $buttonRoute ?>" role="button">
      Add Biography
    </a>
  <?php
  }

  ?>
  <h2>No recent biography on file</h2>
<?php

  return;
}


$published = match ($userState->presenter->published) {
  SkillPublishedState::published->value => 'Published',
  default => 'Pending Review'
};

if ($userState->isBioCurrent()) {
  $event = $this->currentEventInfo->description . ' <span class="badge bg-danger">Current</span>';
} else {
  $event = '';
  $eventInfo = null;

  try {
    $eventInfo = new EventInfo(alias: $userState->presenter->event, withUnpublished: true);
    $event = $eventInfo->description;
  } catch (\Exception) {
    $event = '&lt;Unknown Event&gt;';
  }

  $event .= ' <span class="badge bg-info">Not Current</span>';
}

?>
<h2>Biography Summary</h2>

<div class="table-responsive col-12">
  <table class="table table-striped table-dark">
    <thead>
      <tr class="">
        <th scope="col" class="col-4">Entry</th>
        <th scope="col" class="col-8">Value</th>
      </tr>
    </thead>
    <tbody>
      <tr class="">
        <td class="col-4">Event:</td>
        <td class="col-8"><?= $event ?></td>
      </tr>
      <tr>
        <td>State:</td>
        <td><?= $published ?></td>
      </tr>
      <tr>
        <td>Public Name:</td>
        <td><?= $userState->presenter->name ?></td>
      </tr>
      <tr>
        <td>Availability:</td>
        <td><?= implode(',', $userState->presenter->arrival) ?: 'Not Set' ?></td>
      </tr>
      <tr>
        <td>Biography:</td>
        <td><?= $userState->presenter->bio ?></td>
      </tr>
      <tr>
        <td>Photo:</td>
        <td>
          <?php
          if (is_null($userState->presenter->image_preview)) {
            echo "No photo on file";
          } else {
          ?>
            <p class="form-label"><strong>Current Image Preview</strong></p>
            <img src="data:image/jpeg;base64,<?= base64_encode($userState->presenter->image_preview) ?>" />
          <?php
          }
          ?>
        </td>
      </tr>
    </tbody>
  </table>

</div>

<?php
if (!$userState->submissionsOpen):
  if ($userState->isBioCurrent()):
?>
    <h3 class="text-warning text-center border border-danger p-3">Submissions are currently closed. Biographies are in view-only mode.</h3>
  <?php
  else :
    $buttonRoute = Route::_('index.php?option=com_claw&task=presentersubmission.copybio');
    $msg = 'Resubmit for ' .  $this->currentEventInfo->description;
  ?>
    <h3 class="text-warning text-center border border-danger p-3">Submissions are closed, but you may submit a biography.
      After submission, please contact the skills coordinator if you need to update your information.</h3>
    <a name="add-biography" id="add-biography" class="btn btn-danger" href="<?= $buttonRoute ?>" role="button">
      <?= $msg ?>
    </a>
<?php
  endif;
  return;
endif;

?>
<h3 class="text-primary text-center border border-danger p-3">
  Submissions are open for <?= $this->currentEventInfo->description ?>.
  You may add/edit your biography.
</h3>
<?php
if ($userState->isBioCurrent()) {
  $buttonRoute = Route::_('index.php?option=com_claw&view=presentersubmission&id=' . $this->bio->id);
  $msg = 'Edit Biography';
} else {
  $buttonRoute = Route::_('index.php?option=com_claw&task=presentersubmission.copybio');
  $msg = 'Resubmit for ' . $this->currentEventInfo->description;
}
?>
<a name="add-biography" id="add-biography" class="btn btn-danger" href="<?= $buttonRoute ?>" role="button">
  <?= $msg ?>
</a>
