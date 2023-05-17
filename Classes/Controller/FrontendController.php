<?php

declare(strict_types=1);
namespace In2code\Lux\Controller;

use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Domain\Factory\VisitorFactory;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Service\ConfigurationService;
use In2code\Lux\Domain\Service\Email\SendAssetEmail4LinkService;
use In2code\Lux\Domain\Tracker\AbTestingTracker;
use In2code\Lux\Domain\Tracker\AttributeTracker;
use In2code\Lux\Domain\Tracker\CompanyTracker;
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
use In2code\Lux\Exception\FakeException;
use In2code\Lux\Exception\FileNotFoundException;
use In2code\Lux\Utility\BackendUtility;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Throwable;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;

class FrontendController extends ActionController
{
    protected $eventDispatcher;
    protected LoggerInterface $logger;

    public function __construct(EventDispatcherInterface $eventDispatcher, LoggerInterface $logger)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->logger = $logger;
    }

    /**
     * @return void
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
     * @return ResponseInterface
     * @noinspection PhpUnused
     */
    public function dispatchRequestAction(
        string $dispatchAction,
        string $identificator,
        array $arguments
    ): ResponseInterface {
        $configurationService = GeneralUtility::makeInstance(ConfigurationService::class);
        if ($configurationService->getTypoScriptSettingsByPath('general.enable') !== '0') {
            return (new ForwardResponse($dispatchAction))
                ->withArguments(['identificator' => $identificator, 'arguments' => $arguments]);
        }
        return $this->jsonResponse(json_encode(['error' => true, 'status' => 'disabled']));
    }

    /**
     * @param string $identificator
     * @param array $arguments
     * @return ResponseInterface
     * @noinspection PhpUnused
     */
    public function pageRequestAction(string $identificator, array $arguments): ResponseInterface
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
            $companyTracker = GeneralUtility::makeInstance(CompanyTracker::class);
            $companyTracker->track($visitor);
            return $this->jsonResponse(json_encode($this->afterAction($visitor)));
        } catch (Throwable $exception) {
            return $this->jsonResponse(json_encode($this->getError($exception)));
        }
    }

    /**
     * @param string $identificator
     * @param array $arguments
     * @return ResponseInterface
     * @noinspection PhpUnused
     */
    public function fieldListeningRequestAction(string $identificator, array $arguments): ResponseInterface
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
            return $this->jsonResponse(json_encode($this->afterAction($visitor)));
        } catch (Throwable $exception) {
            return $this->jsonResponse(json_encode($this->getError($exception)));
        }
    }

    /**
     * @param string $identificator
     * @param array $arguments
     * @return ResponseInterface
     * @noinspection PhpUnused
     */
    public function formListeningRequestAction(string $identificator, array $arguments): ResponseInterface
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
            return $this->jsonResponse(json_encode($this->afterAction($visitor)));
        } catch (Throwable $exception) {
            return $this->jsonResponse(json_encode($this->getError($exception)));
        }
    }

    /**
     * @param string $identificator
     * @param array $arguments
     * @return ResponseInterface
     * @noinspection PhpUnused
     */
    public function email4LinkRequestAction(string $identificator, array $arguments): ResponseInterface
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
            return $this->jsonResponse(json_encode($this->afterAction($visitor)));
        } catch (Throwable $exception) {
            return $this->jsonResponse(json_encode($this->getError($exception)));
        }
    }

    /**
     * @param string $identificator
     * @param array $arguments
     * @return ResponseInterface
     * @noinspection PhpUnused
     */
    public function downloadRequestAction(string $identificator, array $arguments): ResponseInterface
    {
        try {
            $visitor = $this->getVisitor($identificator);
            $downloadTracker = GeneralUtility::makeInstance(DownloadTracker::class, $visitor);
            $downloadTracker->addDownload($arguments['href'], (int)$arguments['pageUid']);
            return $this->jsonResponse(json_encode($this->afterAction($visitor)));
        } catch (Throwable $exception) {
            return $this->jsonResponse(json_encode($this->getError($exception)));
        }
    }

    /**
     * @param string $identificator
     * @param array $arguments
     * @return ResponseInterface
     * @noinspection PhpUnused
     */
    public function linkClickRequestAction(string $identificator, array $arguments): ResponseInterface
    {
        try {
            $visitor = $this->getVisitor($identificator);
            $linkClickTracker = GeneralUtility::makeInstance(LinkClickTracker::class, $visitor);
            $linkClickTracker->addLinkClick((int)$arguments['linklistenerIdentifier'], (int)$arguments['pageUid']);
            return $this->jsonResponse(json_encode($this->afterAction($visitor)));
        } catch (Throwable $exception) {
            return $this->jsonResponse(json_encode($this->getError($exception)));
        }
    }

    /**
     * @param string $identificator empty means no opt-in yet
     * @return ResponseInterface
     */
    public function redirectRequestAction(string $identificator): ResponseInterface
    {
        try {
            $visitor = $this->getVisitor($identificator);
        } catch (Throwable $exception) {
            try {
                // Empty fingerprint, create visitor on the fly
                $visitor = new Visitor();
            } catch (Throwable $exception) {
                return $this->jsonResponse(json_encode($this->getError($exception)));
            }
        }
        return $this->jsonResponse(json_encode($this->afterAction($visitor)));
    }

    /**
     * @param string $identificator
     * @param array $arguments
     * @return ResponseInterface
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function abTestingRequestAction(string $identificator, array $arguments): ResponseInterface
    {
        try {
            $visitor = $this->getVisitor($identificator);
        } catch (Throwable $exception) {
            try {
                // Empty fingerprint, create visitor on the fly
                $visitor = new Visitor();
            } catch (Throwable $exception) {
                return $this->jsonResponse(json_encode($this->getError($exception)));
            }
        }

        $abTestingTracker = GeneralUtility::makeInstance(AbTestingTracker::class, $visitor);
        $abpagevisit = $abTestingTracker->track((int)$arguments['abTestingPage']);
        $result = $this->afterAction($visitor);
        $result[] = ['action' => 'abPageVisit', 'configuration' => ['record' => $abpagevisit->getUid()]];
        return $this->jsonResponse(json_encode($result));
    }

    /**
     * @param string $identificator
     * @param array $arguments
     * @return ResponseInterface
     * @throws ExceptionDbal
     */
    public function abTestingConversionFulfilledRequestAction(
        string $identificator,
        array $arguments
    ): ResponseInterface {
        try {
            $visitor = $this->getVisitor($identificator);
        } catch (Throwable $exception) {
            try {
                // Empty fingerprint, create visitor on the fly
                $visitor = new Visitor();
            } catch (Throwable $exception) {
                return $this->jsonResponse(json_encode($this->getError($exception)));
            }
        }

        $abTestingTracker = GeneralUtility::makeInstance(AbTestingTracker::class, $visitor);
        $abTestingTracker->conversionFulfilled((int)$arguments['abPageVisitIdentifier']);
        return $this->jsonResponse(json_encode($this->afterAction($visitor)));
    }

    /**
     * @param string $title
     * @param string $text
     * @param string $href
     * @param array $arguments
     * @return ResponseInterface
     * @noinspection PhpUnused
     */
    public function email4linkAction(
        string $title,
        string $text,
        string $href,
        array $arguments = []
    ): ResponseInterface {
        $this->view->assignMultiple([
            'download' => [
                'title' => $title,
                'text' => $text,
                'href' => $href,
                'arguments' => $arguments,
            ],
        ]);
        return $this->jsonResponse(json_encode(['html' => $this->view->render()]));
    }

    /**
     * @return ResponseInterface
     * @noinspection PhpUnused
     */
    public function trackingOptOutAction(): ResponseInterface
    {
        return $this->htmlResponse();
    }

    /**
     * Track visitors with
     * - frontendlogin or from a
     * - luxletter-link
     *
     * @param Visitor $visitor
     * @param array $arguments
     * @return void
     * @throws EmailValidationException
     * @throws IllegalObjectTypeException
     * @throws InvalidConfigurationTypeException
     * @throws UnknownObjectException
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
     * @param Throwable $exception
     * @return array
     */
    protected function getError(Throwable $exception): array
    {
        $this->eventDispatcher->dispatch(
            GeneralUtility::makeInstance(AfterTrackingEvent::class, new Visitor(), 'error', ['error' => $exception])
        );
        if (BackendUtility::isBackendAuthentication() === false) {
            // Log error to var/log/typo3_[hash].log
            $this->logger->warning('Error in FrontendController happened', [
                'code' => $exception->getCode(),
                'message' => $exception->getMessage(),
            ]);
            $exception = new FakeException('Error happened', 1680200937);
        }
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
     * @throws ConfigurationException
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     * @throws FileNotFoundException
     * @throws InvalidConfigurationTypeException
     */
    protected function getVisitor(string $identificator, bool $tempVisitor = false): Visitor
    {
        $visitorFactory = GeneralUtility::makeInstance(VisitorFactory::class, $identificator, $tempVisitor);
        return $visitorFactory->getVisitor();
    }
}
