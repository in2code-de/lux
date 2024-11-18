# Features for testing

This is just an internal lists of features and functions that have to be tested before a major release

## LUX

### Backend

* TYPO3 Dashboard (:white_check_mark:)
* Analysis module (:white_check_mark:)
  * Dashboard (:white_check_mark:)
    * Filter general functionality (:white_check_mark:)
  * Pages/Downloads (:white_check_mark:)
    * Pages: List (:white_check_mark:)
    * Pages: Preview (:white_check_mark:)
    * Pages: Detail (:white_check_mark:)
    * Downloads: List (:white_check_mark:)
    * Downloads: Preview (:white_check_mark:)
    * Downloads: Detail (:white_check_mark:)
    * CSV Download (:white_check_mark:)
    * Filter general functionality (:white_check_mark:)
  * News (:white_check_mark:)
    * List (:white_check_mark:)
    * Preview (:white_check_mark:)
    * Detail (:white_check_mark:)
    * CSV Download (:white_check_mark:)
    * Filter general functionality (:white_check_mark:)
  * Search (:white_check_mark:)
    * List (:white_check_mark:)
    * Preview (:white_check_mark:)
    * Detail (:white_check_mark:)
  * UTM (:white_check_mark:)
    * List (:white_check_mark:)
    * Preview (:white_check_mark:)
    * Detail (:white_check_mark:)
  * LinkListener (:white_check_mark:)
    * List (:white_check_mark:)
    * Preview (:white_check_mark:)
    * Detail (:white_check_mark:)
* Leads module
  * Dashboard (:white_check_mark:)
    * Filter general functionality (:white_check_mark:)
  * Leads
    * List
      * Filter general functionality (:white_check_mark:)
      * Sorting (:white_check_mark:)
    * Preview (:white_check_mark:)
    * Detail (:white_check_mark:)
      * Delete (:white_check_mark:)
* Company
  * Without Wiredminds Token (:white_check_mark:)
    * Add token functionality (:white_check_mark:)
  * With Wiredminds Token (:white_check_mark:)
    * List (:white_check_mark:)
    * Preview (:white_check_mark:)
    * Detail (:white_check_mark:)
* Plugins (:white_check_mark:)
  * Privacy Plugin for Opt-in/Opt-out (:white_check_mark:)
* Campaigns (:white_check_mark:)
* Info module (i icon) (:white_check_mark:)
* Render times (:white_check_mark:)
* "Go enterprise" note (:white_check_mark:)
* PageOverview im Backend (:white_check_mark:)
  * Analysis (:white_check_mark:)
  * Leads (:white_check_mark:)
* RTE
  * Email4link (:white_check_mark:)
  * LinkListener Link (:white_check_mark:)
* Commands

### Frontend

* Page Request (:white_check_mark:)
* Tracking (:white_check_mark:)
  * Page (:white_check_mark:)
  * News (:white_check_mark:)
  * Search (:white_check_mark:)
  * Scoring (:white_check_mark:)
    * Page visit (:white_check_mark:)
    * Download (:white_check_mark:)
  * Category Scoring
    * Page visit (:white_check_mark:)
    * Download (:white_check_mark:)
  * UTM parameters (:white_check_mark:)
* Identification
  * Field mapping (:white_check_mark:)
  * Form mapping (:white_check_mark:)
  * Email4link (:white_check_mark:)
    * With redirect (:white_check_mark:)
    * With email (:white_check_mark:)
    * With "Powered by LUX" (:white_check_mark:)
  * Frontend login (:white_check_mark:)
  * LUXletter
* LinkListener (:white_check_mark:)
* Privacy Plugin for Opt-in/Opt-out (:white_check_mark:)

## LUXenterprise

### Backend

* Campaign module
  * Workflows
    * List (:white_check_mark:)
    * New (:white_check_mark:)
    * Edit (:white_check_mark:)
    * Preview (:white_check_mark:)
    * Detail (:white_check_mark:)
  * A/B Testing
    * List (:white_check_mark:)
    * Preview (:white_check_mark:)
    * Detail (:white_check_mark:)
    * Page module overview (:white_check_mark:)
  * URL Shortener
    * List (:white_check_mark:)
      * Copy to clipboard (:white_check_mark:)
    * New (:white_check_mark:)
    * Edit (:white_check_mark:)
    * QR (:white_check_mark:)
    * Preview (:white_check_mark:)
    * Detail (:white_check_mark:)
  * UTM Generator
    * New (:white_check_mark:)
    * Edit (:white_check_mark:)
    * Filter (:white_check_mark:)
* Plugins (:white_check_mark:)
    * Contextual Content (:white_check_mark:)
    * Hidden Content (:white_check_mark:)
* No "Go enterprise" note (:white_check_mark:)

### Frontend

* URL Shortener (:white_check_mark:)
* Workflows (:white_check_mark:)
  * Open popup on page (:white_check_mark:)
  * Send to slack on page (:white_check_mark:)
  * ... Further pages in Testparcours (:white_check_mark:)
* Contextual Content Plugin (:white_check_mark:)
* Hidden Content Plugin (for Workflows) (:white_check_mark:)
* A/B Tests (:white_check_mark:)
* Shortener (:white_check_mark:)
* API/Interface (:white_check_mark:)
* Commands (:white_check_mark:)
* Email4link without "Powered by LUX" (:white_check_mark:)

# TYPO3 12
LUXenterprise:
- Email4link in FE without ads
