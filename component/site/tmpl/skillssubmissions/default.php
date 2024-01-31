<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Helpers\Bootstrap;
use ClawCorpLib\Helpers\Config;
use ClawCorpLib\Helpers\Helpers;
use ClawCorpLib\Lib\EventInfo;

// Get menu heading information
echo $this->params->get('heading') ?? '<h1>Presenter Submissions</h1>';

ob_start();
BioHtml($this);
$bioHtml = ob_get_contents();
ob_end_clean();

ob_start();
ClassesHtml($this);
$classesHtml = ob_get_contents();
ob_end_clean();

$tabs = [
  'Biography',
  'Classes'
];

$content = [
  $bioHtml,
  $classesHtml
];

$activeTab = Helpers::sessionGet('skills.submission.tab', 'Biography');
Bootstrap::writePillTabs($tabs, $content, $activeTab);

?>
<form action="<?php echo Route::_('index.php?option=com_claw&view=skillssubmissions') ?>" method="post" name="skilllssubmissions" id="skillssubmissions-form" class="form-validate" enctype="multipart/form-data">
  <input type="hidden" name="task" value="" />
  <?php echo HTMLHelper::_('form.token'); ?>
</form>

<?php

function BioHtml(object &$__this)
{
  // Handle easy case where recent bio is not on file
  if ( !property_exists($__this, 'bio') || !property_exists($__this->bio, 'id') ) {
    ?>
      <h2>No recent biography on file</h2>
    <?php
    if ( !$__this->canEditBio && $__this->canAddOnlyBio ):
    ?>
      <h3 class="text-warning">Submissions are closed, but you may submit a biography (typically used for late entry).
        After submission, you will no longer be able to edit it.</h3>
    <?php
    elseif ( !$__this->canEditBio && !$__this->canAddOnlyBio ):
      ?>
      <h3 class="text-warning">Submissions are closed. You may not submit a biography.</h3>
    <?php
    else:
    ?>
      <h3 class="text-warning">Submissions are open for <?php echo $__this->eventInfo->description ?>.
        You may add and edit your biography.</h3>
    <?php
    endif;

    $buttonRoute = Route::_('index.php?option=com_claw&view=presentersubmission&id=0');
    $msg = 'Add Biography';
    ?>
      <a name="add-biography" id="add-biography" class="btn btn-danger" href="<?= $buttonRoute ?>" role="button"><?= $msg ?></a>
    <?php

    return;
  }


  $published = match ($__this->bio->published) {
    0 => 'Unpublished',
    1 => 'Published',
    default => 'Pending Review'
  };

  $isCurrent = $__this->bio->event == Aliases::current(true);

  $eventInfo = new EventInfo($__this->bio->event);
  $event = $eventInfo->description;

  if ($isCurrent) {
    $event .= ' <span class="badge bg-danger">Current</span>';
  } else {
    $event .= ' <span class="badge bg-info">Previous</span>';
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
            <td><?= $__this->bio->name ?></td>
          </tr>
          <tr>
            <td>Biography:</td>
            <td><?= $__this->bio->bio ?></td>
          </tr>
          <tr>
            <td>Photo:</td>
            <td>
              <?php
                $field = $__this->bio->photo;
                if ($field != false && $field !== '') {
                  $file = implode(DIRECTORY_SEPARATOR, [JPATH_ROOT, $field]);
                  if (is_file($file)) {
                    $ts = time();
                ?>
                    <p class="form-label"><strong>Current Image Preview</strong></p>
                    <img src="<?= $field ?>?ts=<?php echo $ts ?>" />
                <?php
                  } else {
                    echo 'No photo on file';
                  }
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
  if ($__this->params->get('se_submissions_open') == 0) :
    if ( ($__this->bio->id ?? 0 != 0) && $isCurrent ) :
    ?>
      <h3 class="text-info">Submissions are currently closed. Biographies are in view-only mode.</h3>
    <?php
    else :
      $buttonRoute = Route::_('index.php?option=com_claw&task=copybio&id=' . $__this->bio->id);
      $msg = 'Resubmit for ' . Config::getTitleMapping()[Aliases::current()];
    ?>
      <h3 class="text-warning">Submissions are closed, but you may submit a biography.
        After submission, please contact the skills coordinator with your updated information.</h3>
      <a name="add-biography" id="add-biography" class="btn btn-danger" href="<?= $buttonRoute ?>" role="button"><?= $msg ?></a>
    <?php
    endif;
  else :
    ?>
    <h3 class="text-warning">Submissions are open for <?php echo $__this->eventInfo->description ?>.
      You may add/edit your biography.</h3>
    <?php
    if ($isCurrent) {
      $buttonRoute = Route::_('index.php?option=com_claw&view=presentersubmission&id=' . $__this->bio->id);
      $msg = 'Edit Biography';
    } else {
      $buttonRoute = Route::_('index.php?option=com_claw&task=copybio&id=' . $__this->bio->id);
      $msg = 'Resubmit for ' . Config::getTitleMapping()[Aliases::current()];
    }
    ?>
    <a name="add-biography" id="add-biography" class="btn btn-danger" href="<?= $buttonRoute ?>" role="button"><?= $msg ?></a>
  <?php

  endif;
}

function ClassesHtml(object &$__this)
{
  $skillRoute = Route::_('index.php?option=com_claw&view=skillsubmission');

  $canSubmit = $__this->params->get('se_submissions_open') != 0;
  $bioIsCurrent = property_exists($__this, 'bio') && property_exists($__this->bio, 'event') && $__this->bio->event == Aliases::current();

  // var_dump($__this->classes);

  ?>
  <?php if (!$canSubmit) : ?>
    <h3 class="text-warning text-center border border-danger p-3">Class submissions are currently closed.</h3>
    <?php else :
    if ($bioIsCurrent) : ?>
      <h3 class="text-warning text-center border border-info p-3">Class submissions are open for <?= $__this->eventInfo->description ?>. You may add and edit your class submissions.</h3>
    <?php else : ?>
      <h3 class="text-warning text-center border border-info p-3">Please submit your bio for <?= $__this->eventInfo->description ?> before adding/editing class descriptions.</h3>
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

        foreach ($__this->classes as $class) {
          ClassRow($class, $canSubmit && $bioIsCurrent);
        }
        ?>
      </tbody>
    </table>
  </div>

  <?php
  if ($canSubmit && $bioIsCurrent):
  ?>
    <a name="add-class" id="add-class" class="btn btn-danger" href="<?php echo $skillRoute ?>&id=0" role="button">Add Class</a>
  <?php
  endif;
}

function ClassRow(object $row, bool $canSubmit)
{
  $button = '';

  $published = match ($row->published) {
    0 => 'Unpublished',
    1 => 'Published',
    default => 'Pending Review'
  };

  if ($canSubmit) {
    if ($row->event == Aliases::current()) {
      $buttonRoute = Route::_('index.php?option=com_claw&view=skillsubmission&id=' . $row->id);
      $msg = 'View/Edit Class';
    } else {
      $buttonRoute = Route::_('index.php?option=com_claw&task=copyskill&id=' . $row->id);
      $msg = 'Resubmit for ' . Config::getTitleMapping()[Aliases::current()];
    }

    $button = <<< HTML
    <a name="edit-class" id="edit-class-{$row->id}" class="btn btn-danger" href="{$buttonRoute}" role="button">{$msg}</a>
HTML;
  }

  $eventInfo = new EventInfo($row->event);

?>
  <tr>
    <td><?= $eventInfo->description ?></td>
    <td><?= $row->title ?></td>
    <td><?= $published ?></td>
    <td><?= $button ?></td>
  </tr>
<?php
}
