<img align="left" src="../../../Resources/Public/Icons/lux.svg" width="50" />

### Symfony Commands (Scheduler Tasks)

This part of the documentation shows you all available Commands in Lux.

Most of the Commands can be called via CLI or via Scheduler Backend Module (directly or via cronjob).

**Overview**

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
* Service commands
  * Calculate scoring for all leads
  * Cache warmup for cache layer


#### Anonymize Leads Command

##### \In2code\Lux\Command\LuxAnonymizeCommand

Will anonymous all recorded data related to Lux in your database.
This is only a function if you want to present your Lux information to others.
Because this function cannot be reverted, please do this only on test systems.

**Note:** This Command can be started from CLI only (not from Scheduler).

Example usage:

```
./vendor/bin/typo3 lux:anonymize
```


#### Cleanup Commands

##### \In2code\Lux\Command\LuxCleanupAllVisitorsCommand

Removes all visitors by truncating all lux tables.
Can be used for some kind of content updates to a development server.

Example usage:

```
./vendor/bin/typo3 lux:cleanupAllVisitors
```

##### \In2code\Lux\Command\LuxCleanupVisitorsByAgeCommand

Removes all visitors (identified and anonymous) where the last visit is older then the given Timestamp (in seconds).
Remove means in this case not deleted=1 but really remove from database.

Example usage:

```
# Remove unknown visitors (and all there related data) where the last activity is older then one year
./vendor/bin/typo3 lux:cleanupVisitorsByAge 31536000
```

##### \In2code\Lux\Command\LuxCleanupUnknownVisitorsByAgeCommand

Removes not identified visitors where the last visit is older then the given Timestamp (in seconds).
Remove means in this case not deleted=1 but really remove from database.

Example usage:

```
# Remove unknown visitors (and all there related data) where the last activity is older then one year
/vendor/bin/typo3 lux:cleanupUnknownVisitorsByAge 31536000
```

##### \In2code\Lux\Command\LuxCleanupVisitorByUidCommand

Removes a single visitor (identified or anonymous) by a given uid.
Remove means in this case not deleted=1 but really remove from database.

Example usage:

```
# Remove visitors (and all there related data) with uid=123
/vendor/bin/typo3 lux:cleanupVisitorByUid 123
```

##### \In2code\Lux\Command\LuxCleanupVisitorsByPropertyCommand

Removes visitors by given attributes (identified or anonymous).
Could be used to remove e.g. bots from your system from time to time.

Example usage:

```
# Remove visitor by email address (1 = exact match)
./vendor/bin/typo3 lux:cleanupVisitorsByProperty email name@mail.org 1

# Remove visitors with userAgents "Googlebot" (0 = like)
./vendor/bin/typo3 lux:cleanupVisitorsByProperty fingerprints.userAgent Googlebot 0

# Remove visitors with referrer from a testdomain (0 = like)
./vendor/bin/typo3 lux:cleanupVisitorsByProperty pagevisits.referrer "test.in2code.de" 0
```


#### Lead Commands

All commands in this class allows you to frequently receive a summary of leads via email (e.g. for your sales team).

##### \In2code\Lux\Command\LuxLeadSendSummaryCommand

To get all leads:
You can specify the receiver email addresses, the timeframe in which you are interested (e.g. "give me all leads of the
last 24h"), if lux should send you only identified leads and a minimum scoring.

Example usage:

```
# Send a summary email within the last week (604800 seconds) to 2 email addresses but only identified leads with a minimum scoring of 10
./vendor/bin/typo3 lux:leadSendSummary alex@mail.org,alex2@mail.org 604800 1 10
```

##### \In2code\Lux\Command\LuxLeadSendSummaryOfLuxCategoryCommand

To get all leads of a lux category:
You can specify the receiver email addresses, the timeframe in which you are interested (e.g. "give me all leads of the
last 24h"), if lux should send you only identified leads and a scoring of a category must be higher then 0.

Example usage:

```
# Send a summary email within the last week (604800 seconds) to an email address but only identified leads with any scoring in category 123
./vendor/bin/typo3 lux:leadSendSummaryOfLuxCategory alex@mail.org 604800 1 123
```

##### \In2code\Lux\Command\LuxLeadSendSummaryOfKnownCompaniesCommand

To get all leads and companies (even anonymous):
You can specify the receiver email addresses, the timeframe in which you are interested (e.g. "give me all leads of the
last 24h"), a minimum scoring and if there must be a scoring in a lux category (higher then 0).

Example usage:

```
# Send a summary email within the last week (604800 seconds) to an email address even with unidentified leads with any scoring in category 123
./vendor/bin/typo3 lux:leadSendSummaryOfKnownCompanies alex@mail.org 604800 0 123
```

Example summary mail for sales with activities of identified and unknown leads of a given timeframe (e.g. the last day):
<img src="../../../Documentation/Images/screenshot_summarymail.png" />


#### Service Commands

##### \In2code\Lux\Command\LuxServiceRecalculateScoringCommand

This command will calculate the scoring of all leads. Normally used if the calculation changes
or if you are using the variable *lastVisitDaysAgo* in Extension Manager configuration of the extension Lux.
In the last case it's recommended to run the calculation once per night.

Example usage:

```
./vendor/bin/typo3 lux:serviceRecalculateScoring
```


##### \In2code\Lux\Command\LuxAutorelateToFrontendUsersCommand

If you have frontenduser records with email addresses, you can create relations between visitor and fe_users records

Example usage:

```
./vendor/bin/typo3 lux:autorelateToFrontendUsers
```


##### \In2code\Lux\Command\LuxCacheWarmupCommand

If you are using a cache layer for some views (must be turned on in general extension configuration), you can warm
up those caches via this command.

Example usage:

```
./vendor/bin/typo3 lux:cachewarmup

# Warmup specific views
./vendor/bin/typo3 lux:cachewarmup lux_LuxAnalysis,lux_LuxLeads

# Warmup all views and pass a domain (if you don't store a domain in siteconfiguration or if you want to use a specific domain for backend requests)
./vendor/bin/typo3 lux:cachewarmup lux_LuxAnalysis,lux_LuxLeads,web_layout https://domainforbackend.de
```

**Note:** Every call of this command will clean all LUX cachelayer caches at the beginning of the task.
**Note2:** This command can only be executed from CLI only
