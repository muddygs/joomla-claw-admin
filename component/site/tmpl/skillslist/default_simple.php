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

if ($this->list_type == 'simple') $this->tabId = '';

foreach ($this->list->tabs->overview['category'] as $tab_item) {
  if (!count($tab_item['ids'])) continue;

?>
  <h2><?= $tab_item['name'] ?></h2>

  <div class="skills">
    <div class="row row-striped">
      <div class="col-lg-6 font-weight-bold order-1">Title</div>
      <div class="col-2 col-lg-2 font-weight-bold order-3 order-lg-2">Day</div>
      <div class="col-8 col-lg-2 font-weight-bold order-2 order-lg-3">Presenter(s)</div>
      <div class="col-2 col-lg-2 font-weight-bold order-4">Survey</div>
    </div>
    <?php

    foreach ($tab_item['ids'] as $classId) {
      /** @var \ClawCorpLib\Skills\Skill */
      $class = $this->list->skillArray[$classId];

      $url = '';

      $title = HTMLHelper::link(
        Route::_('index.php?option=com_claw&view=skillsclass&id=' . $classId) . '&tab=' . $this->tabId,
        $class->track ?
          '<span class="badge rounded-pill text-bg-success">' . strtoupper($class->track) . '</span>&nbsp;' . $class->title :
          $class->title,
        ['class' => 'fs-5']
      );

      $day = $class->day->format('D');
      if (array_key_exists($class->time_slot, $this->time_slots)) {
        $timeSlot = $this->time_slots[$class->time_slot];
      } else {
        continue;
      }

      // Merge presenters
      $presenter_urls = [];
      $owner = true;

      foreach ([$class->presenter_id, ...$class->other_presenter_ids] as $presenter) {
        $link = HTMLHelper::link(
          Route::_('index.php?option=com_claw&view=skillspresenter&id=' . $presenter) . '&tab=' . $this->tabId,
          $this->list->presenterArray[$presenter]->name,
          $owner ? ['class' => 'fs-5'] : ['class' => 'fw-light']
        );

        $presenter_urls[] = $link;
        $owner = false;
      }

      $presenter_links = implode('<br/>', $presenter_urls);
      $survey = '<i class="fa fa-comments fa-2x text-dark" data-bs-toggle="tooltip" data-bs-placement="top" title="Surveys are not open"></i>';

      if ($this->list->survey != '' && $this->enable_surveys) {
        $link = $this->list->survey . '&form[classTitleParam]=' . $class->id;
        $survey = '<a href="' . $link . '" style="color:#ffae00"><i class="fa fa-comments fa-2x"></i></a>';
      }

    ?>
      <div class="row row-striped">
        <div class="col-lg-6 pt-1 pb-1 mt-2 mt-lg-1 mb-2 mb-lg-1 order-1 order-lg-1"><?= $title ?></div>
        <div class="col-2 col-lg-2 pt-1 pb-1 mt-2 mt-lg-1 mb-2 mb-lg-1 order-3 order-lg-2"><?= $day ?></div>
        <div class="col-8 col-lg-2 pt-1 pb-1 mt-2 mt-lg-1 mb-2 mb-lg-1 order-2 order-lg-3"><?= $presenter_links ?></div>
        <div class="col-2 col-lg-2 pt-1 pb-1 mt-2 mt-lg-1 mb-2 mb-lg-1 order-4"><?= $survey ?></div>
      </div>
    <?php

    }

    ?>
  </div>
  <hr />
<?php
}
