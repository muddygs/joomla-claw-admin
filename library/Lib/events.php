<?php

namespace ClawCorpLib\Lib;

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use UnexpectedValueException;

require_once(JPATH_ROOT . '/php/lib/events_current.php');

abstract class clawEventType
{
    const none = 0;
    const main = 1;
    const hotel = 2;
    const vc = 3;
    const refunds = 4;
}

abstract class clawPackageType
{
    const none = 0;
    const attendee = 1;
    const volunteer1 = 2;
    const claw_staff = 3; // Multiple list values
    const event_staff = 4; // Multiple list values
    const event_talent = 5; // Renamed to recruited, Multiple list values
    const vendor_crew = 6;
    const dinner = 7;
    const brunch_fri = 8;
    const brunch_sat = 22;
    const brunch_sun = 23;
    const buffet_wed = 21;
    const buffet_thu = 9;
    const buffet_fri = 10;
    const buffet_sun = 11;
    const meal_combo_all = 24;
    const meal_combo_dinners = 25;
    const volunteer2 = 12;
    const volunteer3 = 13;
    const volunteersuper = 19;
    const educator = 14;
    const day_pass_fri = 15;
    const day_pass_sat = 16;
    const day_pass_sun = 17;
    const pass = 18;
    const vip = 20;

    static public function toString(int $clawPackageType): string{
        switch($clawPackageType) {
            case 0:
                return 'None';
                break;
            case 1:
                return 'Attendee'; break;
            case 3:
                return 'Coordinator'; break;
            case 4:
                return 'Staff'; break;
            case 5:
                return 'Volunteer'; break;
            case 6:
                return 'Vendor Crew'; break;
            case 2:
            case 12:
            case 13:
            case 19:
                return 'Volunteer'; break;
            case 14:
                return 'Educator'; break;
            case 15:
            case 16:
            case 17:
                return 'Day Pass'; break;
            case 18:
                return 'Pass'; break;
            case 20:
                return 'VIP'; break;
            default:
                die("Unknown clawPackageType: ". $clawPackageType);
        }
    }
}

class clawEventInfo {
    var $description;
    var $location;
    var $start_date;
    var $end_date;
    var $prefix;
    var $shiftPrefix;
    var $mainAllowed;
    var $cancelBy;
    var $eventType;
}

class clawEvent
{
    var string $description = '';
    var int $clawPackageType = clawPackageType::none;
    var bool $isMainEvent = false;
    var string $couponKey = '';
    var int $couponValue = 0;
    var int $eventId = 0;
    var int $category = 0;
    var int $minShifts = 0;
    var bool $requiresCoupon = false;
    var array $couponAccessGroups = [];
    var bool $isAddon = false;
    var string $link = '';

    public function __construct(object $e)
    {
        $requiredKeys = [
            'couponKey',
            'description',
            'clawPackageType',
            'isMainEvent',
            'couponValue',
            'eventId',
            'category',
            'minShifts',
            'requiresCoupon',
            'couponAccessGroups'
        ];

        $optionalKeys = [
            'isAddon',
            'link'
        ];

        foreach ($requiredKeys as $k) {
            if (!property_exists($e, $k)) {
                var_dump($e);
                die("clawEvent definition failed on $k");
            }

            $this->$k = $e->$k;
        }
        
        foreach($optionalKeys as $k) {
            if ( property_exists($e, $k)) $this->$k = $e->$k;
        }

        if ( $this->isMainEvent == true && $this->link == '' ) {
            die('Main events require link:'. $this->couponKey);
        }
    }

    public function getEventRow(): ?object
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        
        $query = <<<SQL
SELECT *
FROM #__eb_events
WHERE id = $this->eventId
SQL;
        $db->setQuery($query);

        $results = $db->loadObject();

        return $results;
    }

    public function getCartLink(string $class = '', string $baseURL='/claw-all-events'): string
    {
        if ( '' == $class )
        {
            $class = 'btn btn-primary eb-register-button eb-colorbox-addcart cboxElement';
        }
        
        $pt = microtime(true);
        $eid = $this->eventId;

        $url = <<< HTML
        <a class="$class" href="$baseURL?task=cart.add_cart&id=$eid&pt=$pt">$this->description</a>
HTML;
        return $url;
    }
}

class clawEvents
{
    private $events = [];
    var $mainEventIds = [];
    var $couponRequired = [];
    var $overlapEventCategories = [];
    var $shiftCategories = [];
    private string $clawEventAlias = '';

    private clawEventInfo $clawEventInfo;

    private static $eventIds = null;
    private static $categoryIds = null;
    private static $fieldIds = null;

    public function __construct(string $clawEventAlias, bool $enablePastEvents = false)
    {
        $eventAliases = CLAWALIASES::active;
        if ( $enablePastEvents ) $eventAliases = array_merge($eventAliases, CLAWALIASES::past);

        if ( !in_array($clawEventAlias, $eventAliases)) {
            die(__FILE__.': Invalid event request: '.$clawEventAlias);
        }

        self::mapEventAliases();
        self::mapCategoryAliases();
        self::mapFieldIds();

        $this->clawEventAlias = $clawEventAlias;

        if ( $clawEventAlias != 'refunds' ) {
            $this->defineEventMapping();
        } else {
            $this->defineHistoricEventMapping();
        }

        if ( $this->clawEventInfo->eventType == clawEventType::main ) {
            foreach ( $this->events AS $o ) {
                if ( $o->isMainEvent ) $this->mainEventIds[] = $o->eventId;
                if ( $o->requiresCoupon ) $this->couponRequired[] = $o->eventId;
            }
        }

        $this->mainEventIds = array_unique($this->mainEventIds);
        sort($this->mainEventIds);
        $this->couponRequired = array_unique($this->couponRequired);


        foreach ( CLAWALIASES::overlapCategories AS $v ) {
            $this->overlapEventCategories[] = self::$categoryIds[$v]->id;
        }

        foreach (CLAWALIASES::shiftCategories as $v) {
            $this->shiftCategories[] = self::$categoryIds[$v]->id;
        }
    }

    /**
     * Based on event alias, returns all defined events
     * @return array Array of clawEvent
     */
    public function getClawEvents(): array {
        return $this->events;
    }

    public function castEvent(object $e): clawEvent {
        return $e;
    }

    /**
     * @param string $key Event key to search under
     * @param string $value Value to find
     * @param bool $mainOnly Main events only (by default) IFF clawEvent
     * @return null|object Event object (clawEvent, hotelEvent)
     */
    public function getEventByKey(string $key, string $value, bool $mainOnly = true): ?object
    {
        $result = null;
        $found = 0;
        foreach ($this->events as $e) {
            if ( !property_exists($e, $key)) die(__FILE__.': Unknown key requested: ' . $key);

            if ( get_class($e) == 'clawEvent' && $mainOnly && !$e->isMainEvent) continue;
            
            if ($e->$key == $value) {
                $result = $e;
                $found++;
            }
        }

        if ($found > 1) die('Duplicate results found. Did you load multiple events?');

        return $result;
    }

    /**
     * Returns the clawEvent for a given coupon prefix (e.g., A = attendee event )
     * @param string $couponCode Coupon Prefix Letter
     * @return null|clawEvent 
     */
    public function getEventByCouponCode(string $couponCode): ?clawEvent {
        $result = null;
        $found = 0;
        foreach($this->events AS $e )
        {
            if ( $e->couponKey == $couponCode ) {
                $result = $e;
                $found++;
            }
        }

        if ( $found > 1 ) die('Duplicate coupon codes loaded. Did you load multiple events?');
        if ( 0 == $found ) die('Unknown coupon code requested: '.$couponCode);

        return $result;
    }

    public function getEventByPackageType(int $packageType): clawEvent
    {
        $result = null;
        $found = 0;
        foreach ($this->events as $e) {
            if ($e->clawPackageType == $packageType && $e->isMainEvent) {
                $result = $e;
                $found++;
            }
        }

        if ($found > 1) die('Duplicate package types loaded. Did you load multiple events?');
        if (0 == $found) die('Unknown package type requested: ' . $packageType);

        return $result;
    }

    /**
     * Returns an array of all the enrolled events in this class when initialized
     * @return array List of event IDs
     */
    public function getEventIds(): array {
        $ids = [];
        foreach ( $this->events AS $e ) {
            $ids[] = $e->eventId;
        }
        return $ids;
    }

    public function getClawEventInfo(): clawEventInfo {
        return $this->clawEventInfo;
    }

    /**
     * Provides mapping of event alias to event id
     * @return array Alias to event id mapping
     */
    // public static function getEventIds(): array {
    //     if ( self::$eventIds == null ) self::mapEventAliases();
    //     return self::$eventIds;
    // }

    /**
     * Converts event alias to its id
     * @param string $eventAlias Event alias
     * @param bool $quiet Quietly return 0 if alias does not exist
     * @return int Event ID
     */
    public static function getEventId( string $eventAlias, bool $quiet = false ): int
    {
        $eventAlias = strtolower(trim($eventAlias));

        if ('' == $eventAlias) die(__FILE__ . ': event alias cannot be blank');

        if (null == self::$eventIds) self::mapEventAliases();

        if (array_key_exists($eventAlias, self::$eventIds)) {
            return intval(self::$eventIds[$eventAlias]->id);
        } else {
            if ( $quiet ) return 0;
            throw new UnexpectedValueException(__FILE__. ': Unknown eventAlias: '. $eventAlias);
        }
    }

    /**
     * Given a category alias, return its category id
     * @param string Category alias
     * @return int Category ID
     */
    public static function getCategoryId(string $categoryAlias): int
    {
        return clawEvents::getCategoryIds([$categoryAlias])[0];
    }

    /**
     * Given a list of category aliases, returns array of their ids
     * @param array $categoryAliases Optional list of specific category ids to return
     * @return array Array of category ids
     */
    public static function getCategoryIds(array $categoryAliases): array
    {
        if (self::$categoryIds == null) self::mapCategoryAliases();

        if ( count($categoryAliases) == 0 ) die('List of aliases must be provided');

        $result = [];

        foreach ( $categoryAliases AS $c )
        {
            if ( !array_key_exists($c, self::$categoryIds )) die(__FILE__.": Unknown category $c");
            $result[] = self::$categoryIds[$c]->id;
        }

        return $result;
    }


    /** Returns list of event raw rows AND "total_registrants" for each event
     * @param array $categoryIds Array of category ids
     * @param string $orderBy Any valid database column for eb_events, default "title"
     * @return array Array of objects for "id" and "title" of all events sorted by title
     */
    public static function getEventsByCategoryId(array $categoryIds, clawEventInfo $clawEventInfo, string $orderBy = 'title' ): array
    {
        $db = Factory::getContainer()->get('DatabaseDriver');

        $startDate = $clawEventInfo->start_date;
        $endDate = $clawEventInfo->end_date;

        $qCategoryIds = implode(',', $db->q($categoryIds));

        $query = <<<SQL
        SELECT e.*,
        ( SELECT COUNT(*) FROM `#__eb_registrants` WHERE event_id = e.id AND published=1 ) AS `total_registrants`
        FROM #__eb_events e
        WHERE main_category_id IN ($qCategoryIds)
SQL;

        if ( $clawEventInfo->mainAllowed == true ) {
            $query .= ' AND `event_date` > ' . $db->q($startDate);
            $query .= ' AND `event_end_date` < '.$db->q($endDate);
            $query .= ' AND `published`=1';
        }

        $query .= ' ORDER BY '.$db->qn($orderBy);

        $db->setQuery($query);
        $rows = $db->loadObjectList();

        return $rows;
    }

    public static function getCategoryNames(array $categoryAliases): ?array
    {
        $db = Factory::getContainer()->get('DatabaseDriver');

        $query = $db->getQuery(true);
        $query->select('*')
            ->from($db->qn('#__eb_categories'))
            ->where($db->qn('alias').' IN ('.implode(',', $db->q($categoryAliases)).')');
        $db->setQuery($query);
        $rows = $db->loadObjectList('alias');

        return $rows;
    }

    /**
     * Returns fields ids for array of array aliases
     * @param array $fieldNames Array of field aliases
     * @return array Corresponding field ids
     */
    public static function getFieldIds(array $fieldNames): array {
        if ( count($fieldNames) == 0 ) die(__FILE__.': field name array cannot be blank');

        $results=[];

        foreach( $fieldNames AS $f )
        {
            $results[] = self::getFieldId($f);
        }

        return $results;
    }

    /**
     * Converts field alias to its id
     * @param string $fieldName Field alias
     * @return int Field ID
     */
    public static function getFieldId(string $fieldName): int
    {
        $fieldName = trim($fieldName);

        if ('' == $fieldName) die(__FILE__ . ': field name cannot be blank');

        if ( null == self::$fieldIds ) self::mapFieldIds();

        if (array_key_exists($fieldName, self::$fieldIds)) {
            return intval(self::$fieldIds[$fieldName]->id);
        } else {
            die(__FILE__ . ': field name unknown: ' . $fieldName);
        }
    }

    /**
     * Returns the raw database row for an event
     * @param int $event_id The event row ID
     * @return object Database row as object or null on error
     */
    public static function loadEventRow(int $event_id): ?object
    {
        $db = Factory::getContainer()->get('DatabaseDriver');

        $q = $db->getQuery(true);

        $q->select('*')
            ->from('#__eb_events')
            ->where($db->qn('id').'='.$db->q($event_id));
        $db->setQuery($q);
        return $db->loadObject();
    }

    /**
     * Given an event ID, returns the alias that includes that event, except if mainAllowed is false,
     * which does not make sense in this context in order to return specific event
     * @param int $eventId The event ID
     * @return string Event Aliases
     */
    public static function eventIdToClawEventAlias(int $eventId): string
    {
        $event = self::loadEventRow($eventId);

        self::mapCategoryAliases();
        self::mapEventAliases();

        $dir = JPATH_LIBRARIES . '/claw/Lib/';
        $files = scandir($dir);
        if ( $files === false ) return 0;

        $files = preg_grep('/events_[a-z0-9]+\.php/i', $files);
        if ( $files === false ) return 0;

        foreach( $files AS $file )
        {
            $events = [];
            $info = (object)[];
            $aliasMatch = preg_match('/events_([a-z0-9]+)\.php/i', $file, $matches);

            if ( $aliasMatch != 1 ) continue;
            $alias = $matches[1];

            if ( 'events_current.php' == $file ) continue;

            include($dir.$file);

            if ( $info->mainAllowed == false ) continue;

            // Specific -- failover to date (might not need this loop)
            foreach ( $events AS $e )
            {
                if ( $e->eventId == $eventId) return $alias;
            }

            // Now try to match on date
            if ( $event->event_date >= $info->start_date  && $event->event_end_date <= $info->end_date)
            {
                return $alias;
            }
        }

        die('Could not determine CLAW event alias: '. $eventId);
    }

    private function defineEventMapping(): void
    {
        $events = [];
        $info = (object)[];
        include(JPATH_ROOT.'/php/lib/events_'.$this->clawEventAlias.'.php');

        $clawEventInfo = new clawEventInfo();

        foreach ( array_keys(get_class_vars("clawEventInfo")) AS $k ) {
            if ( !property_exists($info, $k )) {
                var_dump($info);
                die("Event description lacks: $k\n");
            }
            $clawEventInfo->$k = $info->$k;
        }

        $this->events = $events;
        $this->clawEventInfo = $clawEventInfo;
    }

    /**
     * This is a special case, used only for refunds, to identify all events that
     * uses the arrays CLAWALIASES::active and CLAWALIASES::past
     */
    private function defineHistoricEventMapping(): void
    {
        if ( $this->clawEventAlias != 'refunds' ) die('This function can only be used for refunds.');

        $events = [];
        $info = (object)[];
        include(JPATH_ROOT.'/php/lib/events_'.$this->clawEventAlias.'.php');

        $clawEventInfo = new clawEventInfo();

        foreach ( array_keys(get_class_vars("clawEventInfo")) AS $k ) {
            if ( !property_exists($info, $k )) {
                var_dump($info);
                die("Event description lacks: $k\n");
            }
            $clawEventInfo->$k = $info->$k;
        }

        $events = [];
        foreach(array_merge(CLAWALIASES::active, CLAWALIASES::past) AS $alias ) {
            include(JPATH_ROOT.'/php/lib/events_'.$alias.'.php');
        }

        $this->events = $events;
        $this->clawEventInfo = $clawEventInfo;
    }

    private static function mapEventAliases(): void
    {
        if ( self::$eventIds != null) return;

        $db = Factory::getContainer()->get('DatabaseDriver');

        $query = 'SELECT alias,id FROM #__eb_events ORDER BY id';
        $db->setQuery($query);
        self::$eventIds = $db->loadObjectList('alias');

        if (self::$eventIds == null) die('Event alias db error.');
    }

    private static function mapCategoryAliases(): void 
    {
        if ( self::$categoryIds != null ) return;
        $db = Factory::getContainer()->get('DatabaseDriver');

        $query = 'SELECT alias,id FROM #__eb_categories ORDER BY id';
        $db->setQuery($query);
        self::$categoryIds = $db->loadObjectList('alias');

        if (self::$categoryIds == null) die('Category alias db error.');
    }

    private static function mapFieldIds(): void
    {
        if (self::$fieldIds != null) return;
        $db = Factory::getContainer()->get('DatabaseDriver');

        $query = 'SELECT `name`,`id` FROM #__eb_fields ORDER BY id';
        $db->setQuery($query);
        self::$fieldIds = $db->loadObjectList('name');

        if (self::$fieldIds == null) die('Field IDs db error.');
    }

    public static function eventAliasToTitle(string $eventAlias): string
    {
        if ( array_key_exists($eventAlias, CLAWALIASES::eventTitleMapping))
        {
            return CLAWALIASES::eventTitleMapping[$eventAlias];
        }

        return $eventAlias;
    }

    /**
     * Converts a location alias to the location id
     * @param string $locationAlias Location alias
     * @return int Location ID
     */
    public static function getLocationId(string $locationAlias): int
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = 'SELECT `id` FROM #__eb_locations WHERE alias = '. $db->q($locationAlias);
        $db->setQuery($query);
        $result = $db->loadResult();
        return (int)$result;
    }
    
    public function dump(): void
    {
        echo "<pre>*** FIELD IDS\n";
        foreach ( self::$fieldIds as $x ) {
            echo $x->name.',',$x->id."\n";
        }
        echo "*** EVENTS IDS\n";
        foreach ( self::$eventIds as $x ) {
            echo $x->alias.',',$x->id."\n";
        }
        echo '<pre>';
    }
}
