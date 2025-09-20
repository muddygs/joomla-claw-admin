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
use Joomla\CMS\Session\Session;

/** @var Joomla\CMS\Application\AdministratorApplication */
$app = Factory::getApplication();
/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $app->getDocument()->getWebAssetManager();
$wa->useScript('htmx');

$token = Session::getFormToken();

?>
<h1 class="mb-4">Event Copy</h1>

<form action="#" method="post" id="claw-eventcopy-form" name="Event Copy" hx-headers='{"X-CSRF-Token": "<?= $token ?>"}'>
  <div class="row align-items-top">
    <div class="col-12 col-lg-4">
      <?php echo $this->form->renderField('from_event'); ?>
      <?php echo $this->form->renderField('tables'); ?>
      <?php echo $this->form->renderField('delete'); ?>
    </div>
    <div class="col-12 col-lg-4">
      <?php echo $this->form->renderField('to_event'); ?>
    </div>
    <div class="col-12 col-lg-4">
      <input name="copy" id="copy" type="button" value="Copy Event" class="btn btn-info mb-2"
        hx-post="/administrator/index.php?option=com_claw&task=eventcopy.doCopyEvent&format=raw"
        hx-target="#results" />
    </div>
  </div>

  <?php echo HTMLHelper::_('form.token'); ?>
</form>


<hr />
<h2>Action Results:</h2>
<div id="results"></div>
