<?php

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

use ClawCorpLib\Lib\Aliases;

use ClawCorpLib\Helpers\Bootstrap;




?>
<h1>Presenter Submissions</h1>


<?php
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

Bootstrap::writePillTabs($tabs, $content);

function BioHtml(object &$__this)
{
  $presenterRoute = Route::_('index.php?option=com_claw&view=presentersubmission');

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
          <td class="col-8"><?php echo ClawCorpLib\Lib\ClawEvents::eventAliasToTitle($__this->bio->event) ?></td>
        </tr>
        <tr>
          <td>State:</td>
          <td><?php echo $__this->bio->published ?></td>
        </tr>
        <tr>
          <td>Public Name:</td>
          <td><?php echo $__this->bio->name ?></td>
        </tr>
        <tr>
          <td>Biography:</td>
          <td><?php echo $__this->bio->bio ?></td>
        </tr>
      </tbody>
    </table>

  </div>
  
  <?php if ($__this->params->get('se_submissions_open') == 0):
    if ( $__this->bio->id ?? 0 != 0 ):
  ?>
      <h3 class="text-info">Submissions are currently closed. You may view only your biography.</h3>
  <?php
    else:
  ?>
      <h3 class="text-warning">Submissions are closed, but you may submit a biography. 
        After submission, you will no longer be able to edit it.</h3>
  <?php
    endif;
  else:
    ?>
    <h3 class="text-warning">Submissions are open for <?php echo $__this->eventInfo->description ?>. 
    You may add/edit your biography.</h3>
    <a name="add-biography" id="add-biography" class="btn btn-danger" href="<?php echo $presenterRoute ?>" role="button">Add/Edit Biography</a>
  <?php

  endif;

}

function ClassesHtml(object &$__this)
{
  $skillRoute = Route::_('index.php?option=com_claw&view=skillsubmission');

  $canSubmit = $__this->params->get('se_submissions_open') == 0 ? false : true;

  // var_dump($__this->classes);

  ?>
  <div class="table-responsive">
    <h2>Table of Submitted Classes</h2>
    <?php if (!$canSubmit): ?>
      <h1>Class submissions are currently closed.</h1>
    <?php else: ?>
      <h1>Class submissions are open for <?php echo $__this->eventInfo->description ?>. You may add and edit your class submissions.</h1>
    <?php endif; ?>
  </div>

  <div class="table-responsive col-12">
    <table class="table table-striped table-dark">
      <thead>
        <th>Event</th>
        <th>Class Title</th>
        <th>Action(s)</th>
      </thead>
      <tbody>
  <?php

  foreach ($__this->classes AS $class) {
    ClassRow($class, $canSubmit);
  }
  ?>
      </tbody>
    </table>
  </div>

  <a name="add-class" id="add-class" class="btn btn-danger" href="<?php echo $skillRoute ?>" role="button">Add Class</a>
<?php
}

function ClassRow(object $row, bool $canSubmit) {
  $button = '';

  if ( $canSubmit ) {
    if ( $row->event == Aliases::current ) {
      $buttonRoute = Route::_('index.php?option=com_claw&view=skillsubmission&id='. $row->id);
      $msg = 'View/Edit Class';
    } else {
      $buttonRoute = Route::_('index.php?option=com_claw&view=skillsubmission&task=copy&id=' . $row->id);
      $msg = 'Resubmit for '.Aliases::eventTitleMapping[Aliases::current];
    }

    $button = <<< HTML
    <a name="edit-class" id="edit-class-{$row->id}" class="btn btn-danger" href="{$buttonRoute}" role="button">{$msg}</a>
HTML;
  

  }


?>
  <tr>
    <td><?php echo ClawCorpLib\Lib\ClawEvents::eventAliasToTitle($row->event) ?></td>
    <td><?php echo $row->title ?></td>
    <td><?php echo $button ?></td>
  </tr>
<?php
}