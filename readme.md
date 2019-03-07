![LUX](Resources/Public/Icons/lux.svg "LUX")

# Living User eXperience - LUX - the Marketing Automation tool for TYPO3

## Introduction

LUX is an enterprise software solution to fill the gap between your TYPO3-website and a standalone marketing automation
tool. LUX will track, identify, analyse your leads and give the visitors some improved user experience for your website
like showing relevant information at the right time.

\newpage

## Screenshots

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

## Documentation

See the full [documentation](Documentation/Index.md) (technical, marketing and privacy)

## Features

### Tracking

- Page views
- Number of website visits
- Pagefunnel
- First and last visit
- Store attributes from any form on the website
- Enrich information via IP: Country, Region, Company
- Track any asset download

### Identification

- Identify a lead with any webform email field
- Offer via CkEditor plugin a email4link popup (give me your email and we are going to send you the asset via email)
- Automaticly merge cookie-ids on second identification (if cookie was removed)

### Analyses

- Last leads per page
- Dashboard (most important information)
- Listview
- Detailview with pagefunnel and activity-log
- Show pageviews
- Show asset downloads

### Scoring

- General scoring (with individual calculation)
- Category Scoring
- Contextual content (based on category scoring)

### Workflow & User Experience (Enterprise version only)

- Workflow backend module with a GUI and easy extension possibility
- Triggers:
-- On page visit (define on which number of pagevisit)
-- On a minimum scoring
-- If in a time frame
-- If visitor enters a page from a category
-- If visitor gets identified
- Actions:
-- Lightbox with a content element
-- Send an email with lead details
-- Redirect to any URL
-- Send publication to a slack channel

### CommandControllers & Scheduler

- Anonymize leads (for presentations or local development)
- Cleanup commands (to really erase data)
- Lead commands to get a summary mail with last activities
- Service commands (calculate scoring for all leads)

### Privacy Features

- There is a plugin which allows the visitor to opt from tracking
- The doNotTrack header of the browser will be respected (no change to overrule this!)
- Toogle IP anonymize function
- Toggle IP information enrichment over ipapi.com
- Toggle Tracking of Pagevisits
- Toggle Tracking of Downloads
- Toggle Field identification of any form
- Toogle Email4link functionality
- CommandController to anonymize records (for developing or for a presentation)

### Possible Enterprise Features

- Todo: Blacklisting
- Todo: Newsletter tool (replace or extend direct_mail? New tool "luxletter" - usable without Lux?)
- Todo: Contacts (Import?)
- Todo: API (Im- and Export)
- Todo: A/B Tests
- Todo: SocialMedia Connection (Twitter)

## Technical requirements

lux needs minimum *TYPO3 8.7* as a modern basic together with *composer mode*. Every kind of form extension is supported
for the identification feature (powermail, form, formhandler, felogin, etc...).
At the moment it's not possible to use lux without **composer mode**!

## Changelog

| Version    | Date       | State      | Description                                                                        |
| ---------- | ---------- | ---------- | ---------------------------------------------------------------------------------- |
| 2.1.0      | 2019-03-07 | Task       | Small cleanup, Update and use case-insensitive search in telecom provider list     |
| 2.0.3      | 2019-02-26 | Bugfix     | Show module action switcher in T3 9.5, remove unneeded stuff from former releases  |
| 2.0.2      | 2019-02-25 | Bugfix     | Small bugfix for TER uploads                                                       |
| 2.0.1      | 2019-02-25 | Bugfix     | Small fixes with default values if configuration is missing                        |
| 2.0.0      | 2019-02-24 | Task       | Publish lux as community version (without Workflows), removeAll CommandController  |
| 1.24.0     | 2018-11-07 | Task       | Update disallowed telecommunication provider list                                  |
| 1.23.0     | 2018-08-17 | Task       | Update disallowed mail provider and telecommunication list                         |
| 1.22.0     | 2018-07-23 | Task       | Update disallowed mail provider list                                               |
| 1.21.1     | 2018-06-28 | Task       | Code cleanup, update telecommunication provider list, performance improvement      |
| 1.21.0     | 2018-06-28 | Task       | Add blacklist function for onetime-email-accounts in email4link functionality      |
| 1.20.3     | 2018-05-24 | Task       | Update privacy link in a new tab.                                                  |
| 1.20.2     | 2018-05-24 | Task       | Small locallang update.                                                            |
| 1.20.1     | 2018-05-24 | Bugfix     | Small documentation update.                                                        |
| 1.20.0     | 2018-05-24 | Feature    | Bugfixes, Privacy checkbox in email4link, Manual blacklisting.                     |
| 1.19.0     | 2018-04-24 | Task       | Documentation update. Telecommunication provider list update.                      |
| 1.18.0     | 2018-04-21 | Feature    | Introduce summary-mails in command controller.                                     |
| 1.17.0     | 2018-04-12 | Feature    | Publish to multiple slack channels now.                                            |
| 1.16.1     | 2018-04-12 | Bugfix     | Fix for chrome select boxes in workflow module, fix filter select in content view. |
| 1.16.0     | 2018-04-12 | Feature    | Show company from IP-address on different places. Add company trigger.             |
| 1.15.1     | 2018-04-04 | Bugfix     | Allow links in lightboxes now.                                                     |
| 1.15.0     | 2018-04-04 | Task       | Add documentation, Dashboard: Show percentual values. Performance in content view. |
| 1.14.0     | 2018-03-26 | Bugfix     | Small bugfixes (CKeditor Plugin, Dateformat)                                       |
| 1.13.2     | 2018-03-18 | Bugfix     | Small bugfixes.                                                                    |
| 1.13.1     | 2018-03-15 | Bugfix     | Small bugfixes.                                                                    |
| 1.13.0     | 2018-03-14 | Task       | Add css grid for dashboard. Small bugfixes.                                        |
| 1.12.0     | 2018-03-13 | Feature    | Disable tracking if be-user is logged in. Small bugfixes.                          |
| 1.11.0     | 2018-03-12 | Feature    | Some privace features. Some brush up. Add contextual content plugin.               |
| 1.10.0     | 2018-03-10 | Task       | Some small improvements. Add a opt-out plugin.                                     |
| 1.9.0      | 2018-03-08 | Task       | Some changes to see categoryscorings.                                              |
| 1.8.0      | 2018-03-07 | Feature    | Optical refactoring of pageoverview. Bugfix in category scoring.                   |
| 1.7.0      | 2018-03-07 | Feature    | Add identified trigger and slack action.                                           |
| 1.6.0      | 2018-03-06 | Task       | Add categoryscoring. Bugfix: Don't track downloads with email4link twice.          |
| 1.5.1      | 2018-03-05 | Bugfix     | Prevent exception in backend.                                                      |
| 1.5.0      | 2018-03-05 | Task       | Finish workflow modules with initial triggers/actions. Small bugfixes.             |
| 1.4.0      | 2018-03-04 | Task       | Split backend modules, add content analysis, integrate nearly complete workflow    |
| 1.3.0      | 2018-03-02 | Task       | Don't show full download path in frontend with email4download                      |
| 1.2.0      | 2018-03-01 | Task       | Some small fixes in backend analysis show identified and recurring.                |
| 1.1.1      | 2018-02-27 | Bugfix     | Some small fixes in backend analysis and email4link functionality.                 |
| 1.1.0      | 2018-02-26 | Task       | Show more relevant information in detail view. Small fixes.                        |
| 1.0.1      | 2018-02-26 | Bugfix     | Fix some smaller bugs that occurs with live data                                   |
| 1.0.0      | 2018-02-26 | Task       | Initial Release with a stable tracking, identification and analyses                |
