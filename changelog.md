# 2024.4.3 (2024-JAN-31)

  1. Fixed class submissions so that "submissions closed" and "still accepting biography" concepts work per the `config.xml` (admin) fields
  1. Improved readiness for processing RSForm data and implemented first report
  1. Implemented (via already installed Event Booking library) [php-qrcode](https://github.com/chillerlan/php-qrcode) for our badge printing routines
  1. Several `default.xml` files were updated to use `addfieldprefix` so custom fields are now (finally) implemented in global and menu item configs
  1. Added handling of non-`PackageInfo` events, configured in global by Event Booking event category

# 2024.4.2 (2024-JAN-15)

This represents generally full translation of our legacy file-by-file PHP that was good enough for Joomla 3. All configurations are now in database tables and MVC is generally adhered to for all code.

# Version numbering scheme

Each version is tagged to an event date to help track when the code was deployed. Format is:

`year`.`month`.`revision`
