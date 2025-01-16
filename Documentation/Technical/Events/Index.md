![LUX](../../Images/logo_claim.svg#gh-light-mode-only "LUX")
![LUX](../../Images/logo_claim_white.svg#gh-dark-mode-only "LUX")

### Events (PSR-14)

Lux offers a lot of EventDispatchers - a technique to hook into Lux runtime with your extension to extend the
existing functionality.
Contact us if you need further events.

#### List of events

| Dispatcher located in                                 | Event Name                                                                | Description                                                                                                                                    |
|-------------------------------------------------------|---------------------------------------------------------------------------|------------------------------------------------------------------------------------------------------------------------------------------------|
| AttributeTracker::addAttribute()                      | \In2code\Lux\Events\AttributeCreateEvent                                  | This event can be used when an attribute is added to a visitor                                                                                 |
| AttributeTracker::addAttribute()                      | \In2code\Lux\Events\Log\LogVisitorIdentifiedByFieldlisteningEvent         | Do something when a visitor was just identified by method "Fieldlistening"                                                                     |
| AttributeTracker::addAttribute()                      | \In2code\Lux\Events\Log\LogVisitorIdentifiedByFormlisteningEvent          | Do something when a visitor was just identified by method "Formlistening"                                                                      |
| AttributeTracker::addAttribute()                      | \In2code\Lux\Events\Log\LogVisitorIdentifiedByEmail4linkEvent             | Do something when a visitor was just identified by method "Email4link"                                                                         |
| AttributeTracker::addAttribute()                      | \In2code\Lux\Events\Log\LogVisitorIdentifiedByLuxletterlinkEvent          | Do something when a visitor was just identified by method "Luxletterlink"                                                                      |
| AttributeTracker::addAttribute()                      | \In2code\Lux\Events\Log\LogVisitorIdentifiedByFrontendauthenticationEvent | Do something when a visitor was just identified by method "Frontendauthentication"                                                             |
| EventTracker::track()                                 | \In2code\Lux\Events\Log\EventTrackerEvent                                 | Hook into pushes for event tracks                                                                                                              |
| AttributeTracker::getAndUpdateAttributeFromDatabase() | \In2code\Lux\Events\AttributeOverwriteEvent                               | This event can be used when an attribute of a visitor is updated                                                                               |
| DownloadTracker::addDownload()                        | \In2code\Lux\Events\Log\DownloadEvent                                     | This event can be used when a visitor downloads a file                                                                                         |
| FrontendController::afterAction()                     | \In2code\Lux\Events\AfterTrackingEvent                                    | This event is used for every kind of frontend/tracking/ajax request in the Frontend Controller                                                 |
| FrontendController::getError()                        | \In2code\Lux\Events\AfterTrackingEvent                                    | Same as afterAction() but called when an error came up while tracking                                                                          |
| LinkClickTracker::addLinkClick()                      | \In2code\Lux\Events\Log\LinkClickEvent                                    | This event can be used when a linkclick is tracked from LUX                                                                                    |
| NewsTracker::track()                                  | \In2code\Lux\Events\NewsTrackerEvent                                      | This event can be used when a news visit is tracked from LUX                                                                                   |
| PageTracker::trackPage()                              | \In2code\Lux\Events\PageTrackerEvent                                      | This event can be used when a pagevisit is tracked from LUX                                                                                    |
| Readable::__construct                                 | \In2code\Lux\Events\ReadableReferrersEvent                                | This event can be used to extend the readable referrer list                                                                                    |
| SearchTracker::track()                                | \In2code\Lux\Events\Log\SearchEvent                                       | This event can be used when a visitor searches with a searchterm that would be tracked from LUX                                                |
| SendAssetEmail4LinkService::sendMail()                | \In2code\Lux\Events\Log\LogEmail4linkSendEmailEvent                       | This event can be used when email4link function sends an email to the visitor                                                                  |
| SendAssetEmail4LinkService::sendMail()                | \In2code\Lux\Events\Log\LogEmail4linkSendEmailFailedEvent                 | This event can be used when email4link function fails to send an email to the visitor (e.g. file is not allowed, file is not existing, etc...) |
| SendAssetEmail4LinkService::send()                    | \In2code\Lux\Events\SetAssetEmail4LinkEvent                               | This event can be used to manipulate the MailMessage object just before sending email4link mail                                                |
| VisitorFactory::__construct()                         | \In2code\Lux\Events\StopAnyProcessBeforePersistenceEvent                  | This event can be used to stop the persistence process by throwing an exception (e.g. if a UserAgent does not fit, etc...)                     |
| VisitorFactory::getVisitor()                          | \In2code\Lux\Events\VisitorFactoryBeforeCreateNewEvent                    | This event can be used just before a new visitor object will be build                                                                          |
| VisitorFactory::getVisitor()                          | \In2code\Lux\Events\VisitorFactoryAfterCreateNewEvent                     | This event can be used just after a new visitor object was build                                                                               |
| VisitorFactory::createNewVisitor()                    | \In2code\Lux\Events\Log\LogVisitorEvent                                   | This event can be used when a new visitor is persisted                                                                                         |
| VisitorMergeService::merge()                          | \In2code\Lux\Events\VisitorsMergeEvent                                    | This event can be used when visitors were merged (re-identified)                                                                               |

#### Usage of SignalSlots in your extension

Look at the official documentation how to work with PSR-14 events in TYPO3 in general:
https://docs.typo3.org/m/typo3/reference-coreapi/main/en-us/Events/EventDispatcher/Index.html

**Note**: Sometimes it's easier to use a finisher instead of dealing with events (see [Finisher](../Finisher/Index.md))
