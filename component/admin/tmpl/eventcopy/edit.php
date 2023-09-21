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

/** @var Joomla\CMS\Application\AdministratorApplication */
$app = Factory::getApplication();
/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $app->getDocument()->getWebAssetManager();
$wa->useScript('com_claw.copyevent');

?>
<h1 class="mb-4">Event Copy</h1>

<form action="/blaa.php" method="post" name="Event Copy" id="claw-copy-event" class="row g-3">
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
</form>


<hr/>
<h2>Results of Copy:</h2>
<div id="results"></div>

<?php echo HTMLHelper::_('form.token'); ?>
