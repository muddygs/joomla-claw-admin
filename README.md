> This project is not sponsored by or associated with Joomla! project in any way.

# COM_CLAW Joomla 5 components and libraries to support event management for our organization

The main purpose of this code base is to support event management of
our bi-annual conference. We needed a solution that manages our complex
schedule. In support of our event management, the core registration
system we use is called [Event Booking](https://joomdonation.com/joomla-extensions/events-booking-joomla-events-registration.html). However, it handles only about 80% of our requirements.

Each event consists of hundreds of registration events (many are volunteers "shifts"), hotel reservations, and registration packages. In order to maintain these entries within Event Booking, we needed tools to wrap our configuration requirements. Customizations to the internal code of event booking are not maintained in this repository, so some of the functionality cannot be directly applied to a Joomla site with Event Booking.

However, this component architecture may be useful in other ways. Rather than managing articles for hundred of events, our site pivots around database entries for schedule, presenters, class listings, and volunteer management (we generally have several hundred per event).

---
## Current status (2025-JUL-13)

Migration testing on Joomla 5 in progress.

* Build needs to be improved; current process is a bash script to assemble the zip files for installation
* No translations; uncertain if this wll be done since everything for our purposes is in English

### History

We had thousands of lines of PHP targeting Joomla 3 that were migrated to the J4 MVC model. We had been using [Fabrik](https://fabrikar.com/) UI, but we were not confident a Joomla 4/5 version would even happen (it did!), but the decision was made that reliance upon Fabrik is not in the best interest of a fully-dedicated solution. This code base replaces the parts we were using within Fabrik with a Joomla-specific UI, which is primarily for data entry.

---
## Major Component Menu Description

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

* [Joomla! Programmers Documentation](https://manual.joomla.org/docs)

   Core description of use the Joomla APIs

   * [Standard form field types](https://manual.joomla.org/docs/general-concepts/forms-fields/)
   * [Manifest files](https://manual.joomla.org/docs/building-extensions/install-update/installation/manifest/)

---
### License

All code is under GNU/GPL v2, the same as Joomla. More information [here](https://docs.joomla.org/Joomla_Licenses).
