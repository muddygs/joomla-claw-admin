<?php

namespace ClawCorpLib\Lib;

\defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use ClawCorpLib\Enums\EventPackageTypes;

class ClawEvent
{
    var string $description = '';
    var EventPackageTypes $clawPackageType = EventPackageTypes::none;
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


