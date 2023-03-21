<?php

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

?>
<h1>Presenter Submissions</h1>

<form action="<?php echo Route::_('index.php?option=com_claw'); ?>" method="post" name="adminForm" id="adminForm">

<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

<h2>Biography</h2>

<a name="add-biography" id="add-biography" class="btn btn-danger" href="/index.php/skills-menu/presenter?view=presentersubmission" role="button">Add Biography</a>

<div class="table-responsive">
<h2>Table of Submitted Classes</h2>
</div>
<a name="add-class" id="add-class" class="btn btn-danger" href="#" role="button">Add Class</a>


<?php echo $this->pagination->getListFooter(); ?>

<input type="hidden" name="task" value="">
<input type="hidden" name="boxchecked" value="0">
<?php echo HTMLHelper::_('form.token'); ?>

</form>