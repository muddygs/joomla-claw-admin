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

$addBioButton = true;
// Handle easy case where recent bio is not on file
if (!property_exists($this, 'bio') || !property_exists($this->bio, 'id')) {
  if (!$this->canSubmit && $this->canAddOnlyBio):
?>
    <h3 class="text-warning text-center border border-danger p-3">
      Submissions are closed, but you may submit a biography (typically used for late entry).
      After submission, you will no longer be able to edit it.
    </h3>
  <?php
  elseif (!$this->canSubmit && !$this->canAddOnlyBio):
    $addBioButton = false;
  ?>
    <h3 class="text-warning text-center border border-danger p-3">
      Biography submissions are currently closed.
    </h3>
  <?php
  else:
  ?>
    <h3 class="text-primary text-center border border-danger p-3">
      Submissions are open for <?= $this->currentEventInfo->description ?>.
      You may add and edit your biography.
    </h3>
  <?php
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


$published = match ($this->bio->published) {
  0 => 'Unpublished',
  1 => 'Published',
  default => 'Pending Review'
};

$isCurrent = $this->bio->event == $this->currentEventInfo->alias;

if ($isCurrent) {
  $event = $this->currentEventInfo->description . ' <span class="badge bg-danger">Current</span>';
} else {
  $eventInfo = new EventInfo($this->bio->event);
  $event = $eventInfo->description;
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
        <td><?= $this->bio->name ?></td>
      </tr>
      <tr>
        <td>Biography:</td>
        <td><?= $this->bio->bio ?></td>
      </tr>
      <tr>
        <td>Photo:</td>
        <td>
          <?php
          $field = $this->bio->image_preview ?? null;
          if (!is_null($field)) {
            $ts = time();
          ?>
            <p class="form-label"><strong>Current Image Preview</strong></p>
            <img src="<?= $field ?>?ts=<?php echo $ts ?>" />
          <?php
          } else {
            echo 'No photo on file';
          }
          ?>
        </td>
      </tr>
    </tbody>
  </table>

</div>

<?php
if (!$this->canSubmit):
  if (($this->bio->id ?? 0 != 0) && $isCurrent) :
?>
    <h3 class="text-warning text-center border border-danger p-3">Submissions are currently closed. Biographies are in view-only mode.</h3>
  <?php
  else :
    $buttonRoute = Route::_('index.php?option=com_claw&task=copybio&id=' . $this->bio->id);
    $msg = 'Resubmit for ' .  $this->currentEventInfo->description;
  ?>
    <h3 class="text-warning text-center border border-danger p-3">Submissions are closed, but you may submit a biography.
      After submission, please contact the skills coordinator with your updated information.</h3>
    <a name="add-biography" id="add-biography" class="btn btn-danger" href="<?= $buttonRoute ?>" role="button">
      <?= $msg ?>
    </a>
  <?php
  endif;
else :
  ?>
  <h3 class="text-primary text-center border border-danger p-3">
    Submissions are open for <?= $this->currentEventInfo->description ?>.
    You may add/edit your biography.
  </h3>
  <?php
  if ($isCurrent) {
    $buttonRoute = Route::_('index.php?option=com_claw&view=presentersubmission&id=' . $this->bio->id);
    $msg = 'Edit Biography';
  } else {
    $buttonRoute = Route::_('index.php?option=com_claw&task=copybio&id=' . $this->bio->id);
    $msg = 'Resubmit for ' . $this->currentEventInfo->description;
  }
  ?>
  <a name="add-biography" id="add-biography" class="btn btn-danger" href="<?= $buttonRoute ?>" role="button">
    <?= $msg ?>
  </a>
<?php

endif;
