<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

use ClawCorpLib\Helpers\Bootstrap;
use ClawCorpLib\Helpers\Helpers;

// Get menu heading information
echo $this->params->get('heading') ?? '<h1>Presenter Submissions</h1>';

$bioHtml = $this->loadTemplate('bio');
$classesHtml = $this->loadTemplate('classes');

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
