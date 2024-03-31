# 24.4.11 (2024-MAR-31)

  1. Added printer configuration for the 3 separate badge types to support different printer scenarios
  1. Fixed bug showing bio submission button when submissions closed

# 24.4.10 (2024-MAR-27)

  1. Added mod_claw_skillslist module
  1. Fix: ScheduleModel now handles selection of event poster image

# 24.4.9 (2024-MAR-21)

  1. Updated formatting and styling of classes listing
  1. Updated formatting and styling of badge printing
  1. Fixed ordering of sponsors in schedule based on sponsorship level
  1. Circumvented error dump bug affecting error reporting
  1. Fixed CVS export of classes excluding class descriptions
  1. mod_claw_schedule: removed sponsor logos due to kiosk space constraints (names only)

# 24.4.8 (2024-MAR-15)

  1. Added mod_claw_schedule
  1. Added alternative layout support to schedule site component
  1. Fixed deployment bugs, especially with date handling when insert into db
  1. Enchanced "referrer" tracking in registration process
  1. mod_claw_vendors: fixed install xml

# 24.4.7 (2024-MAR-07)

  1. Split meal events configuration set in EventInfo into distinct values of Event Booking categories
     * Updated db table and propagated to Checkin process
  1. Added ordering reset button to vendors
  1. Moved ajax handlers from DisplayController to CheckinController
     * Moved static functions from Checkin into CheckinModel in parallel
  1. Moved a few event listeners set in checkin_events.ts directly into HTML attributes (onclick/onchange)
  1. Fixed compatibility issues with Cassiopia template in TabFerret.
  1. mod_claw_vendors updated to DI model

# 24.4.6 (2024-FEB-28)

  1. Split badge printing into attendee, volunteer, and all others
  1. Fixed issue with bio/class submission where EventInfo/EventAlias was not getting set correctly
  1. Fixed problem with skills class listing
  1. Made use of "show rooms" and "show surveys" in class listing
  1. Fixed cart preprocessing not performing shift counts
  1. Updated mod_claw_regbuttons, mod_claw_sponsors to use new dependency injection (DI)
  1. Created mod_claw_tabferret to display articles and modules in Bootstrap 5 layouts of tab, tab vertical, accordian, and carousel
     * TODO: This is worth releasing as a separate module

# 24.4.5 (2024-FEB-20)

  1. Improved event deployment, especially for sponsorships that do not have specific end/start dates
  1. Fixed problem with retrieving event IDs that require a main event in order to register
  1. Tab Ferret module beginnings (not quite ready for live deployment)

# 24.4.4 (2024-FEB-16) - Not deployed

  1. Revamped Config/EventConfig methods
  1. Removed "\_current_" and "\_all_" as values utilized where ClawEventsListField is utilized
  1. Fixed save bug in ScheduleModel due to not converting "day" to SQL format in save()
  1. Added column to #__claw_field_values for event-by-event tracking of presets
  1. Implemented interfaces for editing #__claw_field_values
  1. Fixed bugs with handling of "coupon only" PackageInfo items causing incorrect event assignments on coupons and dereferencing of linked PackageInfo event id

# 24.4.3 (2024-JAN-31)

  1. Fixed class submissions so that "submissions closed" and "still accepting biography" concepts work per the `config.xml` (admin) fields
  1. Improved readiness for processing RSForm data and implemented first report
  1. Implemented (via already installed Event Booking library) [php-qrcode](https://github.com/chillerlan/php-qrcode) for our badge printing routines
  1. Several `default.xml` files were updated to use `addfieldprefix` so custom fields are now (finally) implemented in global and menu item configs
  1. Added handling of non-`PackageInfo` events, configured in global by Event Booking event category

# 24.4.2 (2024-JAN-15)

This represents generally full translation of our legacy file-by-file PHP that was good enough for Joomla 3. All configurations are now in database tables and MVC is generally adhered to for all code.

# Version numbering scheme

Each version is tagged to an event date to help track when the code was deployed. Format is:

`year`.`month`.`revision`
