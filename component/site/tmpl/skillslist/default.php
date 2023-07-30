<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

use ClawCorpLib\Lib\Aliases;
use ClawCorpLib\Helpers\Bootstrap;
use ClawCorpLib\Helpers\Skills;

// Get menu heading information
echo $this->params->get('heading') ?? '';
$eventAlias = $this->params->get('event_alias') ?? Aliases::current;
$listType = $this->params->get('list_type') ?? 'simple';

echo "Skill list"; return;

SimpleClassHtml($this);

function SimpleClassHtml(object &$x)
{
  $query = 'SELECT * FROM `#__fabrik_se_classes` WHERE `published` = 1 AND `event` = '.$db->q(CLAWALIASES::current).' ORDER BY `category`,`title`';
  $db->setQuery($query);
  $rows = $db->loadObjectList('id');
  
  if ( $rows === false || count($rows)<1 )
  {
    ?>
    <p class="text-info">No classes available to display.</p>
    <?php
    return;
  }
  
  $query = 'SELECT parent_id,presenter FROM `#__fabrik_se_classes_repeat_presenter` WHERE `parent_id` IN ( '.implode(',',array_keys($rows)).')';
  $db->setQuery($query);
  $repeats = $db->loadAssocList();
  
  $parentIds = array_column($repeats, 'parent_id');
  
  $presenterIds = array_unique(array_column($repeats, 'presenter'), SORT_NUMERIC);
  $query = 'SELECT id,name FROM #__fabrik_se_presenters WHERE id IN ('.join(',',$presenterIds).')';
  $db->setQuery($query);
  $presenterCache = $db->loadAssocList('id');
  
  $currentCategory = "";
  foreach ( $rows as $row )
  {
    $presenter_links = [];
    $presenters = array_search_values($row->id, $parentIds);
    foreach ( $presenters as $key => $presenterId )
    {
      $presenterId = $repeats[$key]['presenter'];
      $presenter_links[] = $presenterCache[$presenterId]['name'];
    }
  
  
    $day = [
      -1 => 'TBD',
      1 => 'Friday',
      2 => 'Saturday',
      3 => 'Sunday'
    ][$row->day];
      
    $classtime ='';
    
    if ( 1 == $row->day || 2 == $row->day )
    {
      if ( $row->stime < 1200 )
      {
        $day .= ' AM';
      }
      else
      {
        $day .= ' PM';
      }
    }		
      
    // $title = $row->title;
    $url = '/component/fabrik/details/6/'.$row->id;
    $title = "<a href=\"$url\" class=\"skills-link\">".$row->title.'</a>';
    $category = $row->category;
      
    if ( $currentCategory != $category )
    {
      if ( $currentCategory != "" )
      {
        tableFooter();
      }
      
      $currentCategory = $category;
      tableHeader($mapping, $currentCategory);
    }
  //<td class="col-3">$day</div>
  ?>
  <tr class="d-flex">
  <td class="col-6"><?php echo $title ?>&nbsp;<i class="fa fa-chevron-right"></i></div>
  <td class="col-3"><?php echo $day ?></div>
  <td class="col-3"><?php echo implode('<br/>',$presenter_links) ?></div>
  </tr>
  <?php
  }
    
  tableFooter();
  
  function tableHeader($mapping, $category)
  {
    if ( array_key_exists($category, $mapping) )
    {
      $category = $mapping[$category];
    }
    else
    {
      $category = 'unknown: ';
    }
    
    echo "<h2>$category</h2>";
  ?>
  <div class="table-responsive">
  <table class="table table-striped table-dark">
    <thead>
    <tr class="d-flex">
      <th class="col-6">Title</div>
      <th class="col-3">Day</div>
      <th class="col-3">Presenter(s)</div>
    </tr>
    </thead>
    <tbody>
  <?php
  }
  
  function tableFooter()
  {
  ?>
    </tbody>
  </table>
  </div> <!-- class=table-responsive -->
  <?php
    return;
  }
}
