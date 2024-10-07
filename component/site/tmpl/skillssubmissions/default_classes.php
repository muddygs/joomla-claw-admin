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

$skillRoute = Route::_('index.php?option=com_claw&view=skillsubmission');


?>
<?php if (!$this->canSubmit) : ?>
  <h3 class="text-warning text-center border border-danger p-3">
    Class submissions are currently closed.
  </h3>
  <?php else :
  if ($this->bioIsCurrent) : ?>
    <h3 class="text-warning text-center border border-info p-3">
      Class submissions are open for <?= $this->currentEventInfo->description ?>. You may add and edit your class submissions.
    </h3>
  <?php else : ?>
    <h3 class="text-warning text-center border border-info p-3">
      Please submit your bio for <?= $this->currentEventInfo->description ?> before adding/editing class descriptions.
    </h3>
  <?php endif; ?>
<?php endif; ?>

<div class="table-responsive col-12">
  <table class="table table-striped table-dark">
    <thead>
      <th>Event</th>
      <th>Class Title</th>
      <th>Status</th>
      <th>Action(s)</th>
    </thead>
    <tbody>
      <?php

      foreach ($this->classes as $class) {
        $this->row = $class;
        echo $this->loadTemplate('class');
      }
      ?>
    </tbody>
  </table>
</div>

<?php
if ($this->canSubmit && $this->bioIsCurrent):
?>
  <a name="add-class" id="add-class" class="btn btn-danger" href="<?= $skillRoute ?>&id=0" role="button">Add Class</a>
<?php
endif;
