![LUX](Resources/Public/Icons/lux.svg "LUX")

# Living User eXperience - LUX - the Marketing Automation tool for TYPO3

## Introduction

LUX is an enterprise software solution to fill the gap between your TYPO3-website and a standalone marketing automation
tool. LUX will track, identify, analyse your leads and give the visitors some improved user experience for your website
like showing relevant information at the right time.
LUX will not set any cookies.

## Screenshots

TYPO3 dashboard (for TYPO3 10 with package "typo3/cms-dashboard"):\
\
![Example dashboard of TYPO3 10](Documentation/Images/screenshot_typo3dashboard.png "TYPO3 Dashboard")

Example dashboard overview:\
\
![Example dashboard overview](Documentation/Images/screenshot_dashboard.png "Dashboard")

Example leadlist:\
\
![Leadlist](Documentation/Images/screenshot_list.png "Leadlist")

Show some details:\
\
![Some Details](Documentation/Images/screenshot_list2.png "Some details")

Show relevant information in page view:\
\
![Relevant information in page view](Documentation/Images/screenshot_pageoverview.png "Page Overview")

Workflow - Define your own trigger(s) and combine them via AND or OR (Enterprise version only):\
\
![Workflow trigger](Documentation/Images/screenshot_workflow_trigger.png "Workflow Trigger")

Workflow - Do one or more Actions if a trigger is activated (Enterprise version only):\
\
![Workflow action](Documentation/Images/screenshot_workflow_action.png "Workflow action")

Ask for the visitors email-address when he/she wants to download an asset:\
\
![Email 4 Link](Documentation/Images/screenshot_email4link.png "E-Mail for Link")

... with a CK editor plugin:\
\
![Email for Link with CKEditor](Documentation/Images/screenshot_email4link_ckeditor_plugin.png "with CKeditor")

## What's new in 7.0.0?

* TYPO3 10 general support (with new TypoScript conditions, symfony Commands, MailMessage class for 9 and 10)
* Replace cookieID with fingerprinting method (no more cookies for identification) - multi domain and platform identification
* Replace functional cookies with local storage records (no more functional cookies)
* Add a lot of dashboard widgets
* Performance feature
* Update documentation with new stuff

## Documentation

See the full [documentation](Documentation/Index.md) (technical, marketing and privacy)

## Features

### Extensions for lux

* luxenterprise for individual workflows: https://www.in2code.de/produkte/lux-typo3-marketing-automation/
* luxletter for email marketing aspects: https://github.com/in2code-de/luxletter

### Tracking

* Page views
* Number of website visits
* Pagefunnel
* First and last visit
* Store attributes from any form on the website
* Enrich information via IP: Country, Region, Company
* Track any asset download

### Identification

* Identify a lead with any webform email field
* Identify a lead while listening to complete form submits
* Offer via CkEditor plugin a email4link popup (give me your email and we are going to send you the asset via email)
* Identify a lead via click in a newsletter email sent by [Extension luxletter](https://github.com/in2code-de/luxletter)
* Identify a lead automatically via frontend login
* Automatically merge legacy cookie-ids or different fingerprints on new identifications
* Multi-Domain, Multi-Device and Multi-Platorm tracking

### Analyses

* TYPO3 Dashboard supported
* Last leads per page
* Dashboard (most important information)
* Listview
* Detailview with pagefunnel and activity-log
* Show pageviews
* Show asset downloads

### Scoring

* General scoring (with individual calculation)
* Category Scoring
* Contextual content (based on category scoring)

### Workflow & User Experience (Enterprise version only)

* Workflow backend module with a GUI and easy extension possibility
* Triggers:
  * On page visit (define on which number of pagevisit)
  * On a minimum scoring
  * When lead reaches a categoryscoring
  * When lead enters a page of a given category
  * If in a time frame
  * If a lead gets identified
  * If lead company given
  * Use your own expressions for doing even mor individual stuff (with Symfony Expression Language)
  * Limit to a start action (page visit, download, form submit, etc...)
* Actions:
  * Lightbox with a content element
  * Load a content element and show it on the current page
  * Hide or show an element of the current page
  * Send an email with lead details
  * Redirect to any URL
  * Send publication to a slack channel
  * Send lead to your CRM via interface connection
  * Blacklist a visitor

### CommandControllers & Scheduler

* Anonymize leads (for presentations or local development)
* Cleanup commands (to really erase data from database)
  * Remove all visitors
  * Remove all visitors by age
  * Remove unknown visitors by age
  * Remove a defined visitor by uid
  * Remove visitors by a given property
* Lead commands to get a summary mail for your sales team
  * Send an overall summary
  * Send a summary mail with known companies
  * Send a summary mail by a lux category
* Service commands (calculate scoring for all leads)

### Privacy Features

* There is a plugin which allows the visitor to opt-out from tracking
* It's also possible to use opt-in instead of opt-out
* The doNotTrack header of the browser will be respected (hardcoded - no change to overrule this!)
* Toogle IP anonymize function
* Toggle IP information enrichment over ipapi.com
* Toggle Tracking of Pagevisits
* Toggle Tracking of Downloads
* Toggle Field identification of any form
* Toogle Email4link functionality
* CommandController to anonymize records (for developing or for a presentation)
* Blacklist functionality
* Workflow blacklist action

### Upcoming todos

* Channel detection with individual GET-params like &lc=emailsignature or &lc=googleadscampaign1
* Crawler detection e.g. jaybizzle/crawler-detect in StopTracking.php

### Possible Enterprise Features in the future

* Todo: Contacts (Import?)
* Todo: API (Im- and Export)
* Todo: A/B Tests
* Todo: SocialMedia Connection (Twitter)

Interested? Call us!

## Technical requirements

lux needs minimum *TYPO3 9.5* as a modern basic together with *composer mode*. Every kind of form extension is supported
for the identification feature (powermail, form, formhandler, felogin, etc...).
At the moment it's not possible to use lux without **composer mode**!

## Breaking changes !!!

| Version                     | Situation                                           | Upgrade instructions                                                                                                                                                                      |
| --------------------------- | --------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| From former versions to 7.x | Cookie-Table was replaced with a Fingerprint-Table  | Call your TYPO3 upgrade wizard. There will be one more step that will copy values from _idcookie to _fingerprint table. Note that CommandControllers are replaced by Symfony Commands!    |
| From former versions to 3.x | The visitor object can handle more cookies now      | After updating use the update button in extension manager of if you have a lot of data stored, you can also use the LuxUpdateCommandController to prevent timeouts                        |

## Changelog

| Version    | Date       | State      | Description                                                                                                                                                                                |
| ---------- | ---------- | ---------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| 7.2.3      | 2020-04-22 | Bugfix     | Add css class in backend modules to fix view in TYPO3 10.4.                                                                                                                                |
| 7.2.2      | 2020-04-21 | Bugfix     | Change mimetypes of dynamically generated Javascript from application/javascript to text/javascript for older browsers.                                                                    |
| 7.2.1      | 2020-04-20 | Bugfix     | Support content in popups also with typenum in siteconfiguration (only relevant for in2code/luxenterprise). Small cleanup.                                                                 |
| 7.2.0      | 2020-04-17 | Task       | Update for TYPO3 10.4 LTS and it's dashboard breaking changes.                                                                                                                             |
| 7.1.0      | 2020-04-03 | Feature    | Allow errorhandling in workflows and finishers now.                                                                                                                                        |
| 7.0.2      | 2020-04-03 | Bugfix     | In some cases fingerprints are not calculated in time: Extend time and don't track if there is an empty fingerprint.                                                                       |
| 7.0.1      | 2020-03-27 | Bugfix     | Version of luxletter could not detected correctly. Only relevant if lux was used with luxletter.                                                                                           |
| 7.0.0 !!!  | 2020-03-21 | Task       | Support TYPO3 10 and new dashboard module. Don't set cookies any more. Recognize visitors by fingerprinting now. Performance update.                                                       |
| 6.3.0      | 2019-09-14 | Task       | Small update of the telecommunication provider list                                                                                                                                        |
| 6.2.0      | 2019-08-27 | Feature    | Updated disallowed mail provider list and updated telecommunication provider list                                                                                                          |
| 6.1.0      | 2019-08-14 | Feature    | Stop tracking of google bots before records get persisted, add some new signals, small cleanup                                                                                             |
| 6.0.0      | 2019-08-10 | Feature    | New dashboard views: Add identified leads per month, add overall statistics, Html refactoring, Don't show wrong default image for leads, New VH for luxenterprise, Documentation update    |
| 5.1.0      | 2019-08-08 | Task       | Performance update lead list, Keep filter, Don't show duplicates in page content, Add new task to delete a visitor by any property, Disallowed mail providers update, Cleanup tasks bugfix |
| 5.0.0      | 2019-07-31 | Task       | Opt-in functionality, Luxletter support, Identification via frontend-login                                                                                                                 |
| 4.1.2      | 2019-07-25 | Bugfix     | Fix email4link email sending functionality on some edge cases                                                                                                                              |
| 4.1.1      | 2019-07-13 | Bugfix     | Bugfix for PHP 7.3 - Fluid errors in backend modules                                                                                                                                       |
| 4.1.0      | 2019-07-07 | Task       | Toggle ckeditor configuration, don't add if ckeditor is not installed, fix typo                                                                                                            |
| 4.0.0      | 2019-06-14 | Task       | Include concept of finishers, don't ask again if identified with email4download                                                                                                            |
| 3.0.0 !!!  | 2019-06-10 | Task       | Multi device tracking, form listening, show browser and os information of leads                                                                                                            |
| 2.5.0      | 2019-06-04 | Task       | Some preperations for luxenterprise                                                                                                                                                        |
| 2.4.0      | 2019-06-03 | Feature    | Replace eos with expression-language, Add url f. workflows, doc update, php cleanup                                                                                                        |
| 2.3.1      | 2019-05-20 | Bugfix     | Show correct last visited date in lead list in backend                                                                                                                                     |
| 2.3.0      | 2019-04-17 | Feature    | Move client to serverside check for disallowed mail providers, small fixes                                                                                                                 |
| 2.2.0      | 2019-03-07 | Feature    | Show status in extension manager settings, Show lead name of unidentified leads                                                                                                            |
| 2.1.0      | 2019-03-07 | Task       | Small cleanup, Update and use case-insensitive search in telecom provider list                                                                                                             |
| 2.0.3      | 2019-02-26 | Bugfix     | Show module action switcher in T3 9.5, remove unneeded stuff from former releases                                                                                                          |
| 2.0.2      | 2019-02-25 | Bugfix     | Small bugfix for TER uploads                                                                                                                                                               |
| 2.0.1      | 2019-02-25 | Bugfix     | Small fixes with default values if configuration is missing                                                                                                                                |
| 2.0.0      | 2019-02-24 | Task       | Publish lux as community version (without Workflows), removeAll CommandController                                                                                                          |
| 1.24.0     | 2018-11-07 | Task       | Update disallowed telecommunication provider list                                                                                                                                          |
| 1.23.0     | 2018-08-17 | Task       | Update disallowed mail provider and telecommunication list                                                                                                                                 |
| 1.22.0     | 2018-07-23 | Task       | Update disallowed mail provider list                                                                                                                                                       |
| 1.21.1     | 2018-06-28 | Task       | Code cleanup, update telecommunication provider list, performance improvement                                                                                                              |
| 1.21.0     | 2018-06-28 | Task       | Add blacklist function for onetime-email-accounts in email4link functionality                                                                                                              |
| 1.20.3     | 2018-05-24 | Task       | Update privacy link in a new tab.                                                                                                                                                          |
| 1.20.2     | 2018-05-24 | Task       | Small locallang update.                                                                                                                                                                    |
| 1.20.1     | 2018-05-24 | Bugfix     | Small documentation update.                                                                                                                                                                |
| 1.20.0     | 2018-05-24 | Feature    | Bugfixes, Privacy checkbox in email4link, Manual blacklisting.                                                                                                                             |
| 1.19.0     | 2018-04-24 | Task       | Documentation update. Telecommunication provider list update.                                                                                                                              |
| 1.18.0     | 2018-04-21 | Feature    | Introduce summary-mails in command controller.                                                                                                                                             |
| 1.17.0     | 2018-04-12 | Feature    | Publish to multiple slack channels now.                                                                                                                                                    |
| 1.16.1     | 2018-04-12 | Bugfix     | Fix for chrome select boxes in workflow module, fix filter select in content view.                                                                                                         |
| 1.16.0     | 2018-04-12 | Feature    | Show company from IP-address on different places. Add company trigger.                                                                                                                     |
| 1.15.1     | 2018-04-04 | Bugfix     | Allow links in lightboxes now.                                                                                                                                                             |
| 1.15.0     | 2018-04-04 | Task       | Add documentation, Dashboard: Show percentual values. Performance in content view.                                                                                                         |
| 1.14.0     | 2018-03-26 | Bugfix     | Small bugfixes (CKeditor Plugin, Dateformat)                                                                                                                                               |
| 1.13.2     | 2018-03-18 | Bugfix     | Small bugfixes.                                                                                                                                                                            |
| 1.13.1     | 2018-03-15 | Bugfix     | Small bugfixes.                                                                                                                                                                            |
| 1.13.0     | 2018-03-14 | Task       | Add css grid for dashboard. Small bugfixes.                                                                                                                                                |
| 1.12.0     | 2018-03-13 | Feature    | Disable tracking if be-user is logged in. Small bugfixes.                                                                                                                                  |
| 1.11.0     | 2018-03-12 | Feature    | Some privace features. Some brush up. Add contextual content plugin.                                                                                                                       |
| 1.10.0     | 2018-03-10 | Task       | Some small improvements. Add a opt-out plugin.                                                                                                                                             |
| 1.9.0      | 2018-03-08 | Task       | Some changes to see categoryscorings.                                                                                                                                                      |
| 1.8.0      | 2018-03-07 | Feature    | Optical refactoring of pageoverview. Bugfix in category scoring.                                                                                                                           |
| 1.7.0      | 2018-03-07 | Feature    | Add identified trigger and slack action.                                                                                                                                                   |
| 1.6.0      | 2018-03-06 | Task       | Add categoryscoring. Bugfix: Don't track downloads with email4link twice.                                                                                                                  |
| 1.5.1      | 2018-03-05 | Bugfix     | Prevent exception in backend.                                                                                                                                                              |
| 1.5.0      | 2018-03-05 | Task       | Finish workflow modules with initial triggers/actions. Small bugfixes.                                                                                                                     |
| 1.4.0      | 2018-03-04 | Task       | Split backend modules, add content analysis, integrate nearly complete workflow                                                                                                            |
| 1.3.0      | 2018-03-02 | Task       | Don't show full download path in frontend with email4download                                                                                                                              |
| 1.2.0      | 2018-03-01 | Task       | Some small fixes in backend analysis show identified and recurring.                                                                                                                        |
| 1.1.1      | 2018-02-27 | Bugfix     | Some small fixes in backend analysis and email4link functionality.                                                                                                                         |
| 1.1.0      | 2018-02-26 | Task       | Show more relevant information in detail view. Small fixes.                                                                                                                                |
| 1.0.1      | 2018-02-26 | Bugfix     | Fix some smaller bugs that occurs with live data                                                                                                                                           |
| 1.0.0      | 2018-02-26 | Task       | Initial Release with a stable tracking, identification and analyses                                                                                                                        |
