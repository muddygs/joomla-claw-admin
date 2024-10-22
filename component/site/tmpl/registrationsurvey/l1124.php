<?php

\defined('_JEXEC') or die;

use ClawCorpLib\Helpers\Bootstrap;

$eventLayout = $this->getLayout();
$this->setLayout('common');
echo $this->loadTemplate('heading');
$this->setLayout($eventLayout);

if (is_null($this->mainEvent)):
?>
  <h1>Or Select Registration Type</h1>

  <?php

  $tabs = ['Attendee', 'Volunteer', 'VIP'];
  $html = [];
  $html[] = $this->loadTemplate('attendee');
  $html[] = $this->loadTemplate('volunteer');
  $html[] = $this->loadTemplate('vip');

  if ($this->onsiteActive) {
    $tabs[] = 'Day Passes';
    $html[] = $this->loadTemplate('daypasses');
    $tabs[] = 'Passes';
    $html[] = $this->loadTemplate('passes');
  }

  $tabs[] = 'Other';
  $html[] = $this->loadTemplate('other');


  Bootstrap::writePillTabs($tabs, $html, 'none');

  ?>
  <hr />
<?php
endif;
