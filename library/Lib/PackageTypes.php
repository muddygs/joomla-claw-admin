<?php

namespace ClawCorpLib\Lib;

abstract class PackageTypes
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