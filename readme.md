![LUX](Resources/Public/Icons/lux.svg "LUX")

# Living User eXperience - LUX - the Marketing Automation tool for TYPO3

## Introduction

LUX is an enterprise software solution to fill the gap between your TYPO3-website and a standalone marketing automation
tool. LUX will track, identify, analyse your leads and give the visitors some improved user experience for your website
like showing relevant information at the right time.
LUX will not set any cookies.

## Screenshots

TYPO3 dashboard (for TYPO3 with package "typo3/cms-dashboard"):\
\
![Example TYPO3 dashboard](Documentation/Images/screenshot_typo3dashboard.png "TYPO3 Dashboard")

Example dashboards:\
\
![Example dashboard1](Documentation/Images/screenshot_dashboard.png "Dashboard1")

![Example dashboard2](Documentation/Images/screenshot_analysis_dashboard.png "Dashboard2")

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

## Documentation

See the full [documentation](Documentation/Index.md) (technical, marketing and privacy)

## Features

### Extensions for LUX

* LUXenterprise for individual workflows: https://www.in2code.de/produkte/lux-typo3-marketing-automation/
* LUXletter for email marketing aspects: https://github.com/in2code-de/luxletter

### Tracking

* Page views
* News views
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
* Identify a lead via click in a newsletter email sent by [Extension LUXletter](https://github.com/in2code-de/luxletter)
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
  * Send a summary mail by a LUX category
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

LUX needs minimum *TYPO3 9.5* as a modern basic together with *composer mode*. Every kind of form extension is supported
for the identification feature (powermail, form, formhandler, felogin, etc...).
At the moment it's not possible to use LUX without **composer mode**!

## Changelog and breaking changes

We moved this part (because of the length) to [changelog and breaking chages](Documentation/Technical/Changelog/Index.md)
