<?php

/**
 * @package     ClawCorp
 * @subpackage  com_claw
 *
 * @copyright   (C) 2024 C.L.A.W. Corp. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ClawCorp\Component\Claw\Administrator\View\Shifts;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

// Dump shifts deploy log to bootstrap table
class DeployLogView extends BaseHtmlView
{
  public array $input;

  function display($tpl = null)
  {
    # TODO: put back button here

    $headings = ['Event ID', 'Title', 'Start', 'End', 'Need', 'Weight'];

    if (!count($this->logs)):
?>
      <h2>No events deployed</h2>
    <?php
    else:
    ?>
      <h2>Shift Changes</h2>
      <table class="table">
        <thead>
          <tr>
            <?php
            echo '<th scope="col">' . implode('</th> <th scope="col">', $headings) . '</th>';
            ?>
          </tr>
        </thead>
        <tbody>
          <?php
          foreach ($this->logs as $row):
            echo '<tr><td>' . implode('</td><td>', $row) . '</td></tr>';
          endforeach;
          ?>
        </tbody>
      </table>
    <?php
    endif;

    $headings = ['Event ID', 'Title', 'State', 'Registrants', 'Recommendation'];
    ?>

    <h2>Orphaned Shift Events</h2>
    <table class="table">
      <thead>
        <tr>
          <?php
          echo '<th scope="col">' . implode('</th> <th scope="col">', $headings) . '</th>';
          ?>
        </tr>
      </thead>
      <tbody>
        <?php
        foreach ($this->orphans as $row):
          $output = [];
          $output[] = $row->id;
          $output[] = $row->title;
          $output[] = $row->published ? '<span class="text-danger">Published</span>' : '<span class="text-warning">Unpublished</span>';
          $output[] = $row->memberCount;
          $output[] = $row->memberCount ? '<span class="text-danger">Move registrants to valid shifts</span>' : '<span class="text-warning">Manually delete event</span>';
          echo '<tr><td>' . implode('</td><td>', $output) . '</td></tr>';
        endforeach;
        ?>
      </tbody>
    </table>
<?php
  }
}
