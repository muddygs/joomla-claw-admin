<?php

namespace ClawCorpLib\Lib;

use Joomla\CMS\Factory;
use ClawCorpLib\Enums\EventTypes;

class EventInfo {
    var string $description = '';
    var string $location = '';
    var string $locationAlias = '';
    var string $start_date = '';
    var string $end_date = '';
    var string $prefix = '';
    var string $shiftPrefix = '';
    var bool $mainAllowed = false;
    var string $cancelBy = '';
    var EventTypes $eventType = EventTypes::none;

    /**
     * Event info object with simple date validation if main events are allowed
     * @param object $info 
     * @param int $startdayofweek 1 (default for Monday)
     * @return void 
     */
    public function __construct(object $info, $startdayofweek = 1) {
        foreach ( array_keys(get_object_vars($this)) AS $k ) {
            if ( !property_exists($info, $k )) {
                var_dump($info);
                die("Event description lacks: $k\n");
            }
            $this->$k = $info->$k;
        }

        // Data validation

        // start_date must be a Monday, only if main event process is enabled
        // this allows refund and virtualclaw to exist in their odd separate way

        if ( $this->mainAllowed ) {
            $date = Factory::getDate($this->start_date);
            if ( $date->dayofweek != $startdayofweek ) {
                var_dump($this);
                die("Event Start Date Must Be: " . $startdayofweek.'. Got: '. $date->dayofweek);
            }

            $enddate = $date->modify($this->end_date);
            $this->end_date = $enddate->toSql();
        }
    }

    /**
     * Mimics Date object functionality, returning SQL-formatted result relative to event start date
     * @return string Modified date in SQL format
     */
    public function modify(string $m, bool $validate = true): string|bool {
        $date = Factory::getDate($this->start_date);
        $m = $date->modify($m);

        if ( !is_bool($m))
            return $date->modify($m)->toSql();

        if ( $validate == false ) return false;
    }

    /**
     * Get the Joomla Date object of the event start date
     * @return Date 
     */
    public function getDate(): \Joomla\CMS\Date\Date {
        return Factory::getDate($this->start_date);
    }
}
