<img align="left" src="../../../Resources/Public/Icons/lux.svg" width="50" />

### CommandController

This part of the documentation shows you all available CommandControllers in Lux.

Most of the CommandControllers can be called via CLI or via Scheduler Backend Module (directly or via cronjob).


#### LuxAnonymizeCommandController

* `\In2code\Lux\Command\LuxAnonymizeCommandController::anonymizeAllCommand()` will anonymous all record data.
This is only a function if you want to present your Lux information to others. Because this function cannot be reverted,
please do this only on test systems.

**Note:** This CommandController can be started from CLI only (not from Scheduler).


#### LuxCleanupCommandController

* `\In2code\Lux\Command\LuxCleanupCommandController::removeUnknownVisitorsByAgeCommand(int $timestamp)` Remove not
identified visitors where the last visit is older then the given Timestamp (in seconds).
Remove means in this case not deleted=1 but really remove from database.

* `\In2code\Lux\Command\LuxCleanupCommandController::removeVisitorsByAgeCommand(int $timestamp)` Remove all visitors
(identified and anonymous) where the last visit is older then the given Timestamp (in seconds).
Remove means in this case not deleted=1 but really remove from database.

* `\In2code\Lux\Command\LuxCleanupCommandController::removeVisitorByUidCommand(int $visitorUid)` Remove a single visitor
(identified or anonymous) by a given uid.
Remove means in this case not deleted=1 but really remove from database.

* `\In2code\Lux\Command\LuxCleanupCommandController::removeVisitorByPropertyCommand(string $propertyName, string $propertyValue, bool $exactMatch = true)`
Remove visitors by given attributes (identified or anonymous).
Could be used to remove e.g. bots from your system from time to time.

* `\In2code\Lux\Command\LuxCleanupCommandController::removeAllVisitorsCommand()` Removes all visitors.
Truncate all lux tables. Can be used for some kind of content updates to a development server

Example CLI call (via typo3_console) to remove google bot records:
```
./vendor/bin/typo3cms luxcleanup:removevisitorbyproperty userAgent Googlebot 0
./vendor/bin/typo3cms luxcleanup:removevisitorbyproperty idcookies.userAgent Googlebot 0
```


#### LuxLeadCommandController

All commands in this class allows you to frequently receive a summary of leads via email.

* `\In2code\Lux\Command\LuxLeadCommandController::sendSummaryCommand()` Get all leads:
You can specify the receiver email addresses, the timeframe in which you are interested (e.g. "give me all leads of the
last 24h"), if lux should send you only identified leads and a minimum scoring.

Example CLI call (via typo3_console):
```
./vendor/bin/typo3cms luxlead:sendsummary --emails=mail1@mail.org,mail2@mail.org --timeframe=86400 --identified=1 --minimum-scoring=10
```

* `\In2code\Lux\Command\LuxLeadCommandController::sendSummaryOfLuxCategoryCommand()` Get all leads of a lux category:
You can specify the receiver email addresses, the timeframe in which you are interested (e.g. "give me all leads of the
last 24h"), if lux should send you only identified leads and a scoring of a category must be higher then 0.

Example CLI call (via typo3_console):
```
./vendor/bin/typo3cms luxlead:sendsummaryofluxcategory --emails=mail1@mail.org,mail2@mail.org --timeframe=86400 --identified=1 --lux-category=1
```

* `\In2code\Lux\Command\LuxLeadCommandController::sendSummaryOfKnownCompaniesCommand()`
Get all leads and companies (even anonymous):
You can specify the receiver email addresses, the timeframe in which you are interested (e.g. "give me all leads of the
last 24h"), a minimum scoring and if there must be a scoring in a lux category (higher then 0).

Example CLI call (via typo3_console):
```
./vendor/bin/typo3cms luxlead:sendsummaryofknowncompanies --emails=mail1@mail.org,mail2@mail.org --timeframe=86400 --minimum-scoring=10 --lux-category=1
```

Example summary mail for sales with activities of identified and unknown leads of a given timeframe (e.g. the last day):
<img src="../../../Documentation/Images/screenshot_summarymail.png" />


#### LuxServiceCommandController

`\In2code\Lux\Command\LuxServiceCommandController::reCalculateScoringCommand()` This command will calculate the scoring
of all leads. Normally used if the calculation changes or if you are using the variable *lastVisitDaysAgo* in
Extension Manager configuration of the extension Lux. In the last case it's recommended to run the calculation once
per night.


#### LuxUpdateCommandController

`\In2code\Lux\Command\LuxServiceCommandController::updateCookieStorage()` This command will help you
to not lose your collected data when updating from an older lux major version to 3.x. The used service is the same
as you can start in the extension manager by clicking the update button. We would recommend to use the CLI for this
update and not the extension manager button because of runtime limitations in the apache.
