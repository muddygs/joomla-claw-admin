<?php

namespace ClawCorpLib\Lib;

\defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use ClawCorpLib\Enums\EventPackageTypes;

class ClawEvent
{
    public string $description = '';
    public EventPackageTypes $clawPackageType = EventPackageTypes::none;
    public bool $isMainEvent = false;
    public string $couponKey = '';
    public int $couponValue = 0;
    public int $eventId = 0;
    public int $category = 0;
    public int $minShifts = 0;
    public bool $requiresCoupon = false;
    public array $couponAccessGroups = [];
    public bool $isAddon = false;
    public string $link = '';
    public bool $authNetProfile = false;
    public string $start = '';
    public string $end = '';


    // TODO: Rewrite with named parameters and remove above public properties
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
            'link',
            'authNetProfile',
            'start',
            'end'
        ];

        foreach ($requiredKeys as $k) {
            if (!property_exists($e, $k)) {
                var_dump($e);
                die("ClawEvent definition failed on $k");
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
        $query = $db->getQuery(true);
        $query->select('*')
            ->from('#__eb_events')
            ->where('id = :id')
            ->bind(':id', $this->eventId);
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


