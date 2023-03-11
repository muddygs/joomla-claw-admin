<?php

namespace ClawCorp\Component\Claw\Administrator\Field;

use Joomla\CMS\Form\Field\ListField;
use ClawCorpLib\Helpers\Helpers;

defined('_JEXEC') or die;

class LocationListField extends ListField {
  protected $type = "LocationList";

    /**
     * Customized method to populate the field option groups.
     *
     * @return  array  The field option objects as a nested array in groups.
     */
    protected function getOptions()
    {
      $options = parent::getOptions();

      /*

      */

      $groups = [];

      $locations = Helpers::getLocations($this->getDatabase());
      $index = array_column($locations, 'catid', 'id');
      $titles = array_column($locations, 'value', 'id');

      foreach ( $locations AS $l ) {
        if ( $l->catid == 0 ) {
          $groups[$l->value] = $this->addChildren($index, $titles, $l->id, 1);

          // If nothing, set as option
          continue;
        }

        break; // Once non zero reached, base locations have been exhausted per locations query
      }

      return $groups;
    }

    private function addChildren(&$index, &$titles, int $parent_id, int $level ) {
      // Are there children? If not, return option
      $children = array_search($parent_id, $index);

      if ( in_array($parent_id, $index ) )
      {
        foreach ( $index AS $k => $v ) {
          if ( $v == $parent_id ) {
            
          }
        }

      }
      else
      {
        // return option tag
      }


      //$levelPrefix = str_repeat('- ', max(0, $link->level - 1));
      $groups = [];

      // foreach ( $locations AS $l ) {
      //   if ( $l->catid == $parent_id ) {
      //     $p[$l->value] = $this->addChildren($locations, $l->catid, $level+1);
      //   }
      // }

    }
}