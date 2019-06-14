<img align="left" src="../../../Resources/Public/Icons/lux.svg" width="50" />

### SignalSlots

Lux offers a lot of SignalSlots - a technique to hook into Lux runtime with your extension to extend the functionality. 
Contact us if you need a new signal.

#### List of signals

| Located in                                            | Signal Class Name                                      | Signal Method Name                | Arguments passed                                         | Description                                                                                                                                                               |
| ----------------------------------------------------- | ------------------------------------------------------ | --------------------------------- | ---------------------------------------------------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| FrontendController::afterAction()                     | \In2code\Lux\Controller\FrontendController             | afterTracking                     | Visitor $visitor, string $actionName, array []           | This signal is used for every kind of frontend/tracking/ajax request in the Frontend Controller. The last parameter is an array that can be returned for JavaScript stuff |
| VisitorFactory::createNewVisitor()                    | \In2code\Lux\Domain\Factory\VisitorFactory             | newVisitor                        | Visitor $visitor                                         | This signal can be used when a new visitor is persisted                                                                                                                   |
| SendAssetEmail4LinkService::sendMail()                | \In2code\Lux\Domain\Service\SendAssetEmail4LinkService | email4linkSendEmail               | Visitor $visitor, string $href                           | This signal can be used when email4link function sends an email to the visitor                                                                                            |
| SendAssetEmail4LinkService::sendMail()                | \In2code\Lux\Domain\Service\SendAssetEmail4LinkService | email4linkSendEmail               | Visitor $visitor, string $href                           | This signal can be used when email4link function fails to send an email to the visitor (e.g. file is not allowed, file is not existing, etc...)                           |
| SendAssetEmail4LinkService::send()                    | \In2code\Lux\Domain\Service\SendAssetEmail4LinkService | send                              | MailMessage $message, Visitor $visitor, string $href     | This signal can be used to manipulate the MailMessage just before sending email4link mail                                                                                 |
| VisitorMergeService::merge()                          | \In2code\Lux\Domain\Service\VisitorMergeService        | mergeVisitors                     | QueryResult $visitors                                    | This signal can be used when visitors were merged (reidentified)                                                                                                          |
| AttributeTracker::addAttribute()                      | \In2code\Lux\Domain\Tracker\AttributeTracker           | createNewAttribute                | Attribute $attribute, Visitor $visitor                   | This signal can be used when an attribute is added to a visitor                                                                                                           |
| AttributeTracker::addAttribute()                      | \In2code\Lux\Domain\Tracker\AttributeTracker           | isIdentifiedBy                    | Attribute $attribute, Visitor $visitor                   | This signal can be used when attribute email is added to a visitor                                                                                                        |
| AttributeTracker::getAndUpdateAttributeFromDatabase() | \In2code\Lux\Domain\Tracker\AttributeTracker           | getAndUpdateAttributeFromDatabase | Attribute $attribute, Visitor $visitor                   | This signal can be used when an attribute of a visitor is updated                                                                                                         |
| DownloadTracker::addDownload()                        | \In2code\Lux\Domain\Tracker\DownloadTracker            | addDownload                       | Download $download, Visitor $visitor                     | This signal can be used when a visitor downloads a file                                                                                                                   |
| PageTracker::trackPage()                              | \In2code\Lux\Domain\Tracker\PageTracker                | trackPagevisit                    | Visitor $visitor                                         | This signal can be used when a pagevisit is tracked from Lux                                                                                                              |

#### Usage of SignalSlots in your extension

There are some examples in the web how to use SignalSlot technology from a TYPO3 extension. My favourite is:
https://typo3worx.eu/2017/07/signals-and-slots-in-typo3/

**Note**: In the most cases it's easyier to use a finisher instead of registering a slot 
(see [Finisher](../Finisher/Index.md))
