<?php

use Joomla\CMS\HTML\HTMLHelper;

?>
<div class="border border=info text-white p-3 mx-2 mb-2 rounded">
  <span style="font-size:large;"><i class="fa fa-heart fa-2x"></i>&nbsp;Leather Heart Events:
    Help CLAW volunteers or a community member.</span>
</div>

<?php

$content = '{ebcategory ' . $this->eventConfig->eventInfo->eb_cat_sponsorship[0] . ' toast}';
echo HTMLHelper::_('content.prepare', $content);
