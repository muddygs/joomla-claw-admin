<?php

use Joomla\CMS\HTML\HTMLHelper;

foreach ($this->categories as $id) {
  $content = "{ebcategory {$id} toast}";
  echo HTMLHelper::_('content.prepare', $content);
}
