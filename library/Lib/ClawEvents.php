<?php

namespace ClawCorpLib\Lib;

use Joomla\CMS\Factory;

use ClawCorpLib\Events\AbstractEvent;
use ClawCorpLib\Enums\EventTypes;
use ClawCorpLib\Lib\EventInfo;;
use ClawCorpLib\Lib\Aliases;
use UnexpectedValueException;

\defined('_JEXEC') or die;

class ClawEvents
{
    // private $events = [];
    var $mainEventIds = [];
    var $couponRequired = [];
    var $overlapEventCategories = [];
    var $shiftCategories = [];
    // private string $clawEventAlias = '';

    private AbstractEvent $event;

    private static $eventIds = null;
    private static $categoryIds = null;
    private static $fieldIds = null;

    public function __construct(string $clawEventAlias, bool $enablePastEvents = false)
    {
        $eventAliases = Aliases::active;
        if ( $enablePastEvents ) $eventAliases = array_merge($eventAliases, Aliases::past);

        if ( !in_array($clawEventAlias, $eventAliases)) {
            die(__FILE__.': Invalid event request: '.$clawEventAlias);
        }

        self::mapEventAliases();
        self::mapCategoryAliases();
        self::mapFieldIds();

        // $this->clawEventAlias = $clawEventAlias;

        if ( $clawEventAlias != 'refunds' ) {
            $this->LoadEventClass($clawEventAlias);
        } else {
            die("Refund not implemented");
            //$this->defineHistoricEventMapping();
        }
        
        if ( $this->event->getInfo()->eventType == EventTypes::main ) {
            foreach ( $this->event->getEvents() AS $o ) {
                if ( $o->isMainEvent ) $this->mainEventIds[] = $o->eventId;
                if ( $o->requiresCoupon ) $this->couponRequired[] = $o->eventId;
            }
        }

        $this->mainEventIds = array_unique($this->mainEventIds);
        sort($this->mainEventIds);
        $this->couponRequired = array_unique($this->couponRequired);


        foreach ( Aliases::overlapCategories AS $v ) {
            $this->overlapEventCategories[] = self::$categoryIds[$v]->id;
        }

        foreach (Aliases::shiftCategories as $v) {
            $this->shiftCategories[] = self::$categoryIds[$v]->id;
        }
    }

    public function getEvent() {
        return $this->event;
    }

    /**
     * @param string $key Event key to search under
     * @param string $value Value to find
     * @param bool $mainOnly Main events only (by default) IFF clawEvent
     * @return null|object Event object (ClawEvent)
     */
    public function getEventByKey(string $key, string $value, bool $mainOnly = true): ?\ClawCorpLib\Lib\ClawEvent
    {
        $result = null;
        $found = 0;
        foreach ($this->event->getEvents() as $e) {
            if ( !property_exists($e, $key)) die(__FILE__.': Unknown key requested: ' . $key);

            if ( $mainOnly && !$e->isMainEvent) continue;
            
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
        foreach($this->event->getEvents() AS $e )
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
        foreach ($this->event->getEvents() as $e) {
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
        return $this->event->getEventIds();
    }

    public function getClawEventInfo(): EventInfo {
        return $this->event->getInfo();
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
    public static function getCategoryIds(array $categoryAliases, bool $associative = false): array
    {
        if (self::$categoryIds == null) self::mapCategoryAliases();

        if ( count($categoryAliases) == 0 ) die('List of aliases must be provided');

        $result = [];

        foreach ( $categoryAliases AS $c )
        {
            if ( !array_key_exists($c, self::$categoryIds )) die(__FILE__.": Unknown category $c");

            if ( $associative ) {
                $result[$c] = self::$categoryIds[$c]->id;
            } else {
                $result[] = self::$categoryIds[$c]->id;
            }
        }

        return $result;
    }


    /** Returns list of event raw rows AND "total_registrants" for each event
     * @param array $categoryIds Array of category ids
     * @param string $orderBy Any valid database column for eb_events, default "title"
     * @return array Array of objects for "id" and "title" of all events sorted by title
     */
    public static function getEventsByCategoryId(array $categoryIds, EventInfo $clawEventInfo, string $orderBy = 'title' ): array
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
    public static function eventIdToClawEventAlias(int $eventId): string|bool
    {
        $event = self::loadEventRow($eventId);

        self::mapCategoryAliases();
        self::mapEventAliases();

        $dir = JPATH_LIBRARIES . '/claw/Events';
        $files = scandir($dir);
        if ( $files === false ) return false;

        $files = preg_grep('/[cl][0-9]{4}\.php/i', $files);
        if ( $files === false ) return false;

        foreach( $files AS $file )
        {
            $events = [];
            $info = (object)[];
            $aliasMatch = preg_match('/([a-z0-9]+)\.php/i', $file, $matches);

            if ( $aliasMatch != 1 ) continue;
            $alias = $matches[1];

            if ( 'events_current.php' == $file ) continue;

            $classname = "\\ClawCorpLib\\Events\\$alias";
            /** @var \ClawCorpLib\Events\AbstractEvent */
            $eventlib = new $classname($alias);
            $info = $eventlib->getInfo();

            if ( $info->mainAllowed == false ) continue;

            // Specific -- failover to date (might not need this loop)
            foreach ( $eventlib->getEvents() AS $e )
            {
                if ( $e->eventId == $eventId) return $alias;
            }

            // Now try to match on date
            // Need to process end_date relative to start_date
            $date = Factory::getDate($info->start_date);
            $enddate = $date->modify($info->end_date)->toSql();
            if ( $event->event_date >= $info->start_date  && $event->event_end_date <= $enddate)
            {
                return $alias;
            }
        }

        die('Could not determine CLAW event alias: '. $eventId);
    }

    private function LoadEventClass(string $alias): void
    {
        // $loader = new \Composer\Autoload\ClassLoader();

        // $loader->loadClass("\\ClawCorpLib\\Events\\$alias");

        $classname = "\\ClawCorpLib\\Events\\$alias";
        $this->event = new $classname($alias);

        // $events = [];
        // $info = (object)[];
        // include(JPATH_LIBRARIES.'/claw/Lib/events_'.$this->clawEventAlias.'.php');

        // $clawEventInfo = new EventInfo();

        // foreach ( array_keys(get_class_vars("ClawCorpLib\Lib\EventInfo")) AS $k ) {
        //     if ( !property_exists($info, $k )) {
        //         var_dump($info);
        //         die("Event description lacks: $k\n");
        //     }
        //     $clawEventInfo->$k = $info->$k;
        // }

        // $this->event->events = $events;
        // $this->clawEventInfo = $clawEventInfo;
    }

    /**
     * This is a special case, used only for refunds, to identify all events that
     * uses the arrays Aliases::active and Aliases::past
     */
    private function defineHistoricEventMapping(): void
    {
        if ( $this->event->alias != 'refunds' ) die('This function can only be used for refunds.');

        // $events = [];
        // $info = (object)[];
        // include(JPATH_ROOT.'/php/lib/events_'.$this->event->alias.'.php');

        // $clawEventInfo = new EventInfo();

        // foreach ( array_keys(get_class_vars("clawEventInfo")) AS $k ) {
        //     if ( !property_exists($info, $k )) {
        //         var_dump($info);
        //         die("Event description lacks: $k\n");
        //     }
        //     $clawEventInfo->$k = $info->$k;
        // }

        // $events = [];
        // foreach(array_merge(Aliases::active, Aliases::past) AS $alias ) {
        //     include(JPATH_ROOT.'/php/lib/events_'.$alias.'.php');
        // }

        // $this->event->events = $events;
        // $this->clawEventInfo = $clawEventInfo;
    }

    private static function mapEventAliases(): void
    {
        if ( self::$eventIds != null) return;

        $db = Factory::getContainer()->get('DatabaseDriver');

        $query = 'SELECT alias,id FROM #__eb_events WHERE published=1 ORDER BY id';
        $db->setQuery($query);
        self::$eventIds = $db->loadObjectList('alias');

        if (self::$eventIds == null) die('Event alias db error.');
    }

    private static function mapCategoryAliases(): void 
    {
        if ( self::$categoryIds != null ) return;
        $db = Factory::getContainer()->get('DatabaseDriver');

        $query = 'SELECT alias,id FROM #__eb_categories WHERE published=1 ORDER BY id';
        $db->setQuery($query);
        self::$categoryIds = $db->loadObjectList('alias');

        if (self::$categoryIds == null) die('Category alias db error.');
    }

    private static function mapFieldIds(): void
    {
        if (self::$fieldIds != null) return;
        $db = Factory::getContainer()->get('DatabaseDriver');

        $query = 'SELECT `name`,`id` FROM #__eb_fields WHERE published=1 ORDER BY id';
        $db->setQuery($query);
        self::$fieldIds = $db->loadObjectList('name');

        if (self::$fieldIds == null) die('Field IDs db error.');
    }

    public static function eventAliasToTitle(string $eventAlias): string
    {
        if ( array_key_exists($eventAlias, Aliases::eventTitleMapping))
        {
            return Aliases::eventTitleMapping[$eventAlias];
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
        echo '</pre>';
    }
}
