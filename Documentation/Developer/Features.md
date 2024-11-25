# Features for testing

This is just an internal lists of features and functions that have to be tested before a major release

## LUX

### Backend

* TYPO3 Dashboard
* Analysis module
  * Dashboard
    * Filter general functionality
  * Pages/Downloads
    * Pages: List
    * Pages: Preview
    * Pages: Detail
    * Downloads: List
    * Downloads: Preview
    * Downloads: Detail
    * CSV Download
    * Filter general functionality
  * News
    * List
    * Preview
    * Detail
    * CSV Download
    * Filter general functionality
  * Search
    * List
    * Preview
    * Detail
  * UTM
    * List
    * Preview
    * Detail
  * LinkListener
    * List
    * Preview
    * Detail
* Leads module
  * Dashboard
    * Filter general functionality
  * Leads
    * List
      * Filter general functionality
      * Sorting
    * Preview
    * Detail
      * Delete
* Company
  * Without Wiredminds Token
    * Add token functionality
  * With Wiredminds Token
    * List
    * Preview
    * Detail
* Plugins
  * Privacy Plugin for Opt-in/Opt-out
* Campaigns
* Info module (i icon)
* Render times
* "Go enterprise" note
* PageOverview im Backend
  * Analysis
  * Leads
* RTE
  * Email4link
  * LinkListener Link
* Commands
* TYPO3 search

### Frontend

* Page Request
* Tracking
  * Page
  * News
  * Search
  * Scoring
    * Page visit
    * Download
  * Category Scoring
    * Page visit
    * Download
  * UTM parameters
* Identification
  * Field mapping
  * Form mapping
  * Email4link (:white_check_mark:)
    * With redirect
    * With email
    * With "Powered by LUX"
  * Frontend login
  * LUXletter
* LinkListener
* Privacy Plugin for Opt-in/Opt-out

## LUXenterprise

### Backend

* Campaign module
  * Workflows
    * List
    * New
    * Edit
    * Preview
    * Detail
  * A/B Testing
    * List
    * Preview
    * Detail
    * Page module overview (:white_check_mark:)
    * Download
  * URL Shortener
    * List
      * Copy to clipboard
      * QR-Code
      * Download
    * New
    * Edit
    * Preview
    * Detail
  * UTM Generator
    * New
    * Edit
    * Filter
* Plugins
    * Contextual Content
    * Hidden Content
* No "Go enterprise" note

### Frontend

* URL Shortener
* Workflows
  * Open popup on page
  * Send to slack on page
  * ... Further pages in Testparcours
* Contextual Content Plugin
* Hidden Content Plugin (for Workflows)
* A/B Tests
* Shortener
* API/Interface
* Commands
* Email4link without "Powered by LUX"
