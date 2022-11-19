![LUX](Resources/Public/Icons/lux.svg "LUX")

# Living User eXperience - LUX - the Marketing Automation tool for TYPO3

## Introduction

LUX is an enterprise software solution to fill the gap between your TYPO3-website and a standalone marketing automation
tool. LUX will track, identify, analyse your leads and give the visitors some improved user experience for your website
like showing relevant information at the right time.
LUX will not set any cookies.

<table>
    <tr>
        <th colspan="2" align="center">Screenshots</th>
    </tr>
    <tr>
        <th colspan="2">TYPO3 dashboard (for TYPO3 with package <code>typo3/cms-dashboard</code>)</th>
    </tr>
    <tr>
        <td align="center" colspan="2">
            <img alt="" width="600" src="https://github.com/in2code-de/lux/blob/develop/Documentation/Images/screenshot_typo3dashboard.png">
        </td>
    </tr>
    <tr>
        <th colspan="2">Example dashboards</th>
    </tr>
    <tr>
        <td align="center">
            <img alt="" width="400" src="https://github.com/in2code-de/lux/blob/develop/Documentation/Images/screenshot_dashboard.png">
        </td>
        <td align="center">
            <img alt="" width="400" src="https://github.com/in2code-de/lux/blob/develop/Documentation/Images/screenshot_analysis_dashboard.png">
        </td>
    </tr>
    <tr>
        <th>Leadlist</th>
        <th>Lead details</th>
    </tr>
    <tr>
        <td align="center">
            <img alt="" width="400" src="https://github.com/in2code-de/lux/blob/develop/Documentation/Images/screenshot_list.png">
        </td>
        <td align="center">
            <img alt="" width="400" src="https://github.com/in2code-de/lux/blob/develop/Documentation/Images/screenshot_list2.png">
        </td>
    </tr>
    <tr>
        <th>Show relevant information in page view</th>
        <th>UTM parameters analyse</th>
    </tr>
    <tr>
        <td align="center">
            <img alt="" width="600" src="https://github.com/in2code-de/lux/blob/develop/Documentation/Images/screenshot_pageoverview.png">
        </td>
        <td align="center">
            <img alt="" width="600" src="https://github.com/in2code-de/lux/blob/develop/Documentation/Images/screenshot_analysis_utm.png">
        </td>
    </tr>
    <tr>
        <td align="center">
            <strong>Workflow</strong><br/>
            Define your own trigger(s) and combine them via AND or OR<br/>
            <i>(Enterprise version only)</i>
        </td>
        <td align="center">
            <strong>Workflow</strong><br/>
            Do one or more Actions if a trigger is activated<br/>
            <i>(Enterprise version only)</i>
        </td>
    </tr>
    <tr>
        <td align="center">
            <img alt="" width="400" src="https://github.com/in2code-de/lux/blob/develop/Documentation/Images/screenshot_workflow_trigger.png">
        </td>
        <td align="center">
            <img alt="" width="400" src="https://github.com/in2code-de/lux/blob/develop/Documentation/Images/screenshot_workflow_action.png">
        </td>
    </tr>
    <tr>
        <th>Ask for the visitors email-address when he/she wants to download an asset</th>
        <th>...with a CK editor plugin</th>
    </tr>
    <tr>
        <td align="center">
            <img alt="" width="400" src="https://github.com/in2code-de/lux/blob/develop/Documentation/Images/screenshot_email4link.png">
        </td>
        <td align="center">
            <img alt="" width="400" src="https://github.com/in2code-de/lux/blob/develop/Documentation/Images/screenshot_email4link_ckeditor_plugin.png">
        </td>
    </tr>
</table>

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
* Triggers (already delivered by default):
  * Lead properties: If a lead gets identified
  * Lead properties: When lead reaches a scoring
  * Lead properties: When lead reaches a categoryscoring
  * Lead properties: If lead company given
  * Lead properties: If any property value is given
  * Lead properties: Use your own expressions for doing even mor individual stuff (with Symfony Expression Language)
  * Lead action: On entering a page
  * Lead action: On reading a news
  * Lead action: When lead enters a page of a given category
  * Lead action: When lead enters a page in a given language
  * Lead action: When lead enters a page of a given site
  * Lead source: Check for a given referrer
  * Lead source: Check for a given UTM parameter
  * Miscellaneous: On a defined time
  * Miscellaneous: Limit to a start action (page visit, download, form submit, etc...)
  * Miscellaneous: TYPO3 context
* Actions (already delivered by default):
  * Content manipulation: Open a popup (lightbox) with a content element
  * Content manipulation: Load a content element and show it on the current page
  * Content manipulation: Hide or show an element of the current page
  * Content manipulation: Redirect visitor to another page
  * Content manipulation: Change page title
  * Notification: Sends an email
  * Notification: Publish a message to a slack channel
  * Notification: Send an SMS to a mobile number
  * Notification: Show a push message
  * Lead management: Sets a value for a visitor
  * Lead management: Sets a value after a double opt in confirmation for a visitor
  * Lead management: Add a visitor to a blacklist
  * Data handling: Save values to any table in database
  * Data handling: Send lead information to any interface (e.g. a CRM)

### Commands & Scheduler Tasks

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
