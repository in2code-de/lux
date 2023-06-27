<?php

declare(strict_types=1);
namespace In2code\Lux\Controller;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Exception as ExceptionDbal;
use Exception;
use In2code\Lux\Domain\Factory\VisitorFactory;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Service\Email\SendAssetEmail4LinkService;
use In2code\Lux\Domain\Tracker\AbTestingTracker;
use In2code\Lux\Domain\Tracker\AttributeTracker;
use In2code\Lux\Domain\Tracker\DownloadTracker;
use In2code\Lux\Domain\Tracker\FrontenduserAuthenticationTracker;
use In2code\Lux\Domain\Tracker\LinkClickTracker;
use In2code\Lux\Domain\Tracker\LuxletterlinkAttributeTracker;
use In2code\Lux\Domain\Tracker\NewsTracker;
use In2code\Lux\Domain\Tracker\PageTracker;
use In2code\Lux\Domain\Tracker\SearchTracker;
use In2code\Lux\Events\AfterTrackingEvent;
use In2code\Lux\Exception\ActionNotAllowedException;
use In2code\Lux\Exception\ConfigurationException;
use In2code\Lux\Exception\EmailValidationException;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Object\Exception as ExceptionExtbaseObject;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException;

/**
 * Class FrontendController
 * Todo: Return type ": ResponseInterface" and "return $this->htmlResponse();" when TYPO3 10 support is dropped
 *       for all actions
 */
class FrontendController extends ActionController
{
    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Check for allowed actions
     *
     * @return void
     * @throws NoSuchArgumentException
     * @throws ActionNotAllowedException
     * @noinspection PhpUnused
     */
    public function initializeDispatchRequestAction(): void
    {
        $allowedActions = [
            'pageRequest',
            'fieldListeningRequest',
            'formListeningRequest',
            'email4LinkRequest',
            'downloadRequest',
            'linkClickRequest',
            'redirectRequest',
            'abTestingRequest',
            'abTestingConversionFulfilledRequest',
        ];
        $action = $this->request->getArgument('dispatchAction');
        if (!in_array($action, $allowedActions)) {
            throw new ActionNotAllowedException('Action not allowed', 1518815149);
        }
    }

    /**
     * @param string $dispatchAction
     * @param string $identificator Fingerprint or Local storage hash
     * @param array $arguments
     * @return void
     * @throws StopActionException
     * @noinspection PhpUnused
     */
    public function dispatchRequestAction(string $dispatchAction, string $identificator, array $arguments): void
    {
        $this->forward($dispatchAction, null, null, ['identificator' => $identificator, 'arguments' => $arguments]);
    }

    /**
     * @param string $identificator
     * @param array $arguments
     * @return string
     * @noinspection PhpUnused
     */
    public function pageRequestAction(string $identificator, array $arguments): string
    {
        try {
            $visitor = $this->getVisitor($identificator);
            $this->callAdditionalTrackers($visitor, $arguments);
            $pageTracker = GeneralUtility::makeInstance(PageTracker::class);
            $pagevisit = $pageTracker->track($visitor, $arguments);
            $newsTracker = GeneralUtility::makeInstance(NewsTracker::class);
            $newsTracker->track($visitor, $arguments, $pagevisit);
            $searchTracker = GeneralUtility::makeInstance(SearchTracker::class);
            $searchTracker->track($visitor, $arguments);
            return json_encode($this->afterAction($visitor));
        } catch (Exception $exception) {
            return json_encode($this->getError($exception));
        }
    }

    /**
     * @param string $identificator
     * @param array $arguments
     * @return string
     * @noinspection PhpUnused
     */
    public function fieldListeningRequestAction(string $identificator, array $arguments): string
    {
        try {
            $visitor = $this->getVisitor($identificator);
            $attributeTracker = GeneralUtility::makeInstance(
                AttributeTracker::class,
                $visitor,
                AttributeTracker::CONTEXT_FIELDLISTENING,
                (int)$arguments['pageUid']
            );
            $attributeTracker->addAttribute($arguments['key'], $arguments['value']);
            return json_encode($this->afterAction($visitor));
        } catch (Exception $exception) {
            return json_encode($this->getError($exception));
        }
    }

    /**
     * @param string $identificator
     * @param array $arguments
     * @return string
     * @noinspection PhpUnused
     */
    public function formListeningRequestAction(string $identificator, array $arguments): string
    {
        try {
            $visitor = $this->getVisitor($identificator);
            $values = json_decode($arguments['values'], true);
            if (is_array($values)) {
                $attributeTracker = GeneralUtility::makeInstance(
                    AttributeTracker::class,
                    $visitor,
                    AttributeTracker::CONTEXT_FORMLISTENING,
                    (int)$arguments['pageUid']
                );
                $attributeTracker->addAttributes($values);
            }
            return json_encode($this->afterAction($visitor));
        } catch (Exception $exception) {
            return json_encode($this->getError($exception));
        }
    }

    /**
     * @param string $identificator
     * @param array $arguments
     * @return string
     * @noinspection PhpUnused
     */
    public function email4LinkRequestAction(string $identificator, array $arguments): string
    {
        try {
            $visitor = $this->getVisitor($identificator, true);
            $attributeTracker = GeneralUtility::makeInstance(
                AttributeTracker::class,
                $visitor,
                AttributeTracker::CONTEXT_EMAIL4LINK,
                (int)$arguments['pageUid']
            );
            $values = json_decode((string)$arguments['values'], true);
            if (is_array($values)) {
                $allowedFields = GeneralUtility::trimExplode(
                    ',',
                    $this->settings['identification']['email4link']['form']['fields']['enabled'],
                    true
                );
                $attributeTracker->addAttributes($values, $allowedFields);
            }
            $downloadTracker = GeneralUtility::makeInstance(DownloadTracker::class, $visitor);
            $downloadTracker->addDownload($arguments['href'], (int)$arguments['pageUid']);
            if ($arguments['sendEmail'] === 'true') {
                GeneralUtility::makeInstance(SendAssetEmail4LinkService::class, $visitor, $this->settings)
                    ->sendMail($arguments['href']);
            }
            return json_encode($this->afterAction($visitor));
        } catch (Exception $exception) {
            return json_encode($this->getError($exception));
        }
    }

    /**
     * @param string $identificator
     * @param array $arguments
     * @return string
     * @noinspection PhpUnused
     */
    public function downloadRequestAction(string $identificator, array $arguments): string
    {
        try {
            $visitor = $this->getVisitor($identificator);
            $downloadTracker = GeneralUtility::makeInstance(DownloadTracker::class, $visitor);
            $downloadTracker->addDownload($arguments['href'], (int)$arguments['pageUid']);
            return json_encode($this->afterAction($visitor));
        } catch (Exception $exception) {
            return json_encode($this->getError($exception));
        }
    }

    /**
     * @param string $identificator
     * @param array $arguments
     * @return string
     * @noinspection PhpUnused
     */
    public function linkClickRequestAction(string $identificator, array $arguments): string
    {
        try {
            $visitor = $this->getVisitor($identificator);
            $linkClickTracker = GeneralUtility::makeInstance(LinkClickTracker::class, $visitor);
            $linkClickTracker->addLinkClick((int)$arguments['linklistenerIdentifier'], (int)$arguments['pageUid']);
            return json_encode($this->afterAction($visitor));
        } catch (Exception $exception) {
            return json_encode($this->getError($exception));
        }
    }

    /**
     * @param string $identificator empty means no opt-in yet
     * @return string
     */
    public function redirectRequestAction(string $identificator): string
    {
        try {
            $visitor = $this->getVisitor($identificator);
        } catch (Exception $exception) {
            try {
                // Empty fingerprint, create visitor on the fly
                $visitor = new Visitor();
            } catch (Exception $exception) {
                return json_encode($this->getError($exception));
            }
        }
        return json_encode($this->afterAction($visitor));
    }

    /**
     * @param string $identificator
     * @param array $arguments
     * @return string
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function abTestingRequestAction(string $identificator, array $arguments): string
    {
        try {
            $visitor = $this->getVisitor($identificator);
        } catch (Exception $exception) {
            try {
                // Empty fingerprint, create visitor on the fly
                $visitor = new Visitor();
            } catch (Exception $exception) {
                return json_encode($this->getError($exception));
            }
        }

        $abTestingTracker = GeneralUtility::makeInstance(AbTestingTracker::class, $visitor);
        $abpagevisit = $abTestingTracker->track((int)$arguments['abTestingPage']);
        $result = $this->afterAction($visitor);
        $result[] = ['action' => 'abPageVisit', 'configuration' => ['record' => $abpagevisit->getUid()]];
        return json_encode($result);
    }

    /**
     * @param string $identificator
     * @param array $arguments
     * @return string
     * @throws ExceptionDbal
     */
    public function abTestingConversionFulfilledRequestAction(string $identificator, array $arguments): string
    {
        try {
            $visitor = $this->getVisitor($identificator);
        } catch (Exception $exception) {
            try {
                // Empty fingerprint, create visitor on the fly
                $visitor = new Visitor();
            } catch (Exception $exception) {
                return json_encode($this->getError($exception));
            }
        }

        $abTestingTracker = GeneralUtility::makeInstance(AbTestingTracker::class, $visitor);
        $abTestingTracker->conversionFulfilled((int)$arguments['abPageVisitIdentifier']);
        return json_encode($this->afterAction($visitor));
    }

    /**
     * @return void
     * @noinspection PhpUnused
     */
    public function trackingOptOutAction(): void
    {
    }

    /**
     * Track visitors with
     * - frontendlogin or from a
     * - luxletter-link
     *
     * @param Visitor $visitor
     * @param array $arguments
     * @return void
     * @throws ExceptionExtbaseObject
     * @throws DBALException
     * @throws EmailValidationException
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     */
    protected function callAdditionalTrackers(Visitor $visitor, array $arguments): void
    {
        $authTracker = GeneralUtility::makeInstance(
            FrontenduserAuthenticationTracker::class,
            $visitor,
            AttributeTracker::CONTEXT_FRONTENDUSER,
            (int)$arguments['pageUid']
        );
        $authTracker->trackByFrontenduserAuthentication();

        $luxletterTracker = GeneralUtility::makeInstance(
            LuxletterlinkAttributeTracker::class,
            $visitor,
            AttributeTracker::CONTEXT_LUXLETTERLINK,
            (int)$arguments['pageUid']
        );
        $luxletterTracker->trackFromLuxletterLink();
    }

    /**
     * @param Visitor $visitor
     * @return array
     */
    protected function afterAction(Visitor $visitor): array
    {
        /** @var AfterTrackingEvent $event */
        $event = $this->eventDispatcher->dispatch(
            GeneralUtility::makeInstance(AfterTrackingEvent::class, $visitor, $this->actionMethodName)
        );
        return $event->getResults();
    }

    /**
     * @param Exception $exception
     * @return array
     */
    protected function getError(Exception $exception): array
    {
        $this->eventDispatcher->dispatch(
            GeneralUtility::makeInstance(AfterTrackingEvent::class, new Visitor(), 'error', ['error' => $exception])
        );
        return [
            'error' => true,
            'exception' => [
                'code' => $exception->getCode(),
                'message' => $exception->getMessage(),
            ],
        ];
    }

    /**
     * @param string $identificator
     * @param bool $tempVisitor
     * @return Visitor
     * @throws DBALException
     * @throws ExceptionExtbaseObject
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     * @throws ConfigurationException
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    protected function getVisitor(string $identificator, bool $tempVisitor = false): Visitor
    {
        $visitorFactory = GeneralUtility::makeInstance(VisitorFactory::class, $identificator, $tempVisitor);
        return $visitorFactory->getVisitor();
    }
}
