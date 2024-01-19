<?php
/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2023 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

/** @var Joomla\CMS\Application\AdministratorApplication */
$app = Factory::getApplication();
/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $app->getDocument()->getWebAssetManager();
$wa->useScript('com_claw.copyevent');

?>
<h1 class="mb-4">Event Copy</h1>

<form action="<?= Route::_('index.php?option=com_claw&view=eventcopy'); ?>" id="adminForm" name="adminForm" method="post">
  <div class="row align-items-center">
    <div class="col-12 col-lg-4">
      <?php echo $this->form->renderField('from_event'); ?>
    </div>
    <div class="col-12 col-lg-4">
      <?php echo $this->form->renderField('to_event'); ?>
    </div>
    <div class="col-12 col-lg-4">
      <input name="submit" id="submit" type="button" value="Copy Event" class="btn btn-info mb-2" onclick="copyEvent()"/>
    </div>
  </div>
  
  <input type="hidden" name="task" value="">
  <?php echo HTMLHelper::_('form.token'); ?>
</form>


<hr/>
<h2>Action Results:</h2>
<div id="results"></div>

