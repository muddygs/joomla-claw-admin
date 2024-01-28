<?php
\defined( '_JEXEC' ) or die( 'Restricted access' );

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

$app = Factory::getApplication();
$user  = $app->getIdentity();

if (!$user->authorise('claw.reports', 'com_claw')) {
  return;
}

$commonHeadings = [
  'Submission ID',
  'Item #',
  'Name',
  'Email',
  'Phone',
  'Location',
  'Bio',
  'Artist Statement',
  'Link(s)',
  'Headshot Filename',
  'Title',
  'Medium',
  'Year',
  'Dimension',
  'Price',
  'Image Filename'
];

$filename = 'Art_Show_Export_' . HtmlHelper::date('now', 'Y-m-d_H-i-s') . '.csv';
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="'. $filename . '"');
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
ob_clean();
ob_start();
set_time_limit(0);
ini_set('error_reporting', E_NOTICE);

$fp = fopen('php://output', 'wb');
fputcsv($fp, $commonHeadings);

foreach ( $this->items['submissions'] AS $submissionId => $data ) {
  $columns = [];

  foreach ( $commonHeadings AS $heading ) {
    $columns[$heading] = '';
  }

  $columns['Submission ID'] = $submissionId;
  $columns['Item #'] = 0;
  $columns['Name'] = $data->name;
  $columns['Email'] = $data->email;
  $columns['Phone'] = $data->phone;
  $columns['Location'] = $data->location;
  $columns['Bio'] = $data->bio;
  $columns['Artist Statement'] = $data->artist_statement;
  $columns['Link(s)'] = $data->link;

  if ( property_exists($data,'headshot') ) {
    $columns['Headshot Filename'] = basename($data->headshot);
  }

  // Repeat over the 5 form entries
  for( $i = 1; $i <= 5; $i++) {
    $imageKey = 'image'.$i;

    if ( property_exists($data, $imageKey) ) {
      $columns['Item #'] = $i;

      $columns['Title'] = $data->{'title'.$i};
      $columns['Medium'] = $data->{'medium'.$i};
      $columns['Year'] = $data->{'year'.$i};
      $columns['Dimension'] = $data->{'dimension'.$i};
      $columns['Price'] = $data->{'price'.$i};
      $columns['Image Filename'] = basename($data->{$imageKey});

      fputcsv($fp, $columns);

      // blank these on repeats for readability
      $columns['Bio'] = '---';
      $columns['Link(s)'] = '---';
      $columns['Headshot Filename'] = '---';
      $columns['Artist Statement'] = '---';
    }
  }
}

fclose($fp);

$attachment = ob_get_clean();

if ( false !== $attachment ) echo $attachment;

ob_end_flush();
