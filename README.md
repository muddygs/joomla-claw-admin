# COM_CLAW Joomla 4 components and libraries to support event management for our organization

The main purpose of this code base is to support event management of
our bi-annual conference. We needed a solution that manages our complex
schedule. In support of our event management, the core registration
system we use is called [Event Booking](https://joomdonation.com/joomla-extensions/events-booking-joomla-events-registration.html). However, it handles only about 80% of our requirements.

Each event consists of hundreds of registration events (many are volunteers "shifts"), hotel reservations, and registration packages. In order to maintain these entries within Event Booking, we needed tools to wrap our configuration requirements. Customizations to the internal code of event booking are not maintained in this repository, so some of the functionality cannot be directly applied to a Joomla site with Event Booking.

However, this component architecture may be useful in other ways. Rather than managing articles for hundred of events, our site pivots around database entries for schedule, presenters, class listings, and volunteer management (we generally have several hundred per event).

---
## Current status

We have thousands of lines of PHP targeting Joomla 3 that will be freshly organized following the J4 MVC model. Our current database engine is using the [Fabrik](https://fabrikar.com/) UI, [which may or may not support J4](https://github.com/joomlahenk/fabrik/wiki/Tester-Instructions), but the decision was made that reliance upon Fabrik is not in the best interest of a fully-dedicated solution. This code base replaces the parts we are using within Fabrik with a J4-specific UI, which is primarily for data entry. All display templates are already separated from Fabrik.

* _Important:_ Current code partially implements access controls; these are being added slowly
* Build needs to be improved; current process is a bash script to assemble the zip files for installation
* No translations; uncertain if this wll be done since everything for our purposes is in English
* None of the front-end display templates have been made into modules

---
## Component Menu Description

While probably not in final form, these are functional:

| Menu Item | About |
| --------- | ----- |
| Location  | Allows multi-level locations for (a) city, (b) hotel room for shift management and display within the schedule of events |
| Sponsors  | We have multiple levels of sponsorship. This allows managing sponsors by level and include logos, links, etc. for each |
| Shifts    | This is allows creation of shift "grids" that eventually get deployed within Event Booking |
| Current Items | Global configuration parameters, especially affecting how Event Booking processes registrations |
| Schedules | Supports event listing in frontend |
| Skills | Class listing |
| Presenters | Presenter definitions used for each item in Skills |
| Vendors | List of event vendors |

---
## Links

Information used during development as guides to understanding Joomla 4 Content Management System (CMS)

### Joomla Sites

* [Joomla](https://www.joomla.org/)

   Main Joomla site
* [Joomla Developer Network](https://developer.joomla.org/)

   Main Joomla Dev site

* [MVC Anatomy: Getting Started](https://docs.joomla.org/Special:MyLanguage/J4.x:MVC_Anatomy:_Getting_Started)

   Okay starting point for MVC, but at the time I started, this was an incomplete guide

* [Advanced form guide](https://docs.joomla.org/Advanced_form_guide)

   I still haven't entirely understood this, but it is important to shift grid implementation, eventually

* [Standard form field types](https://docs.joomla.org/Standard_form_field_types)
* [Manifest files](https://docs.joomla.org/Manifest_files)
* [Using own library in your extensions](https://docs.joomla.org/Using_own_library_in_your_extensions/en)

   Old CMS 3, ended up utilizing bootstrap method in `provider.php`. I'm now using the PSR-4 methodology/autoloader.

### Example repositories and development resources

* [Joomla Code](https://joomla-code.com/)

* [astrid/j4examplecode](https://codeberg.org/astrid/j4examplecode/src/branch/t42)

   **Note**: View by specific tag
* [Astrid Gunther](https://blog.astrid-guenther.de/)

* [ceford/j4xdemos-com-countrybase](https://github.com/ceford/j4xdemos-com-countrybase)
---
### License

All code is under GNU/GPL v2, the same as Joomla. More information [here](https://docs.joomla.org/Joomla_Licenses).
