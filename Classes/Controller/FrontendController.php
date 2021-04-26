<?php
declare(strict_types=1);
namespace In2code\Lux\Controller;

use In2code\Lux\Domain\Factory\VisitorFactory;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Service\SendAssetEmail4LinkService;
use In2code\Lux\Domain\Tracker\AttributeTracker;
use In2code\Lux\Domain\Tracker\DownloadTracker;
use In2code\Lux\Domain\Tracker\FrontenduserAuthenticationTracker;
use In2code\Lux\Domain\Tracker\LinkClickTracker;
use In2code\Lux\Domain\Tracker\LuxletterlinkAttributeTracker;
use In2code\Lux\Domain\Tracker\NewsTracker;
use In2code\Lux\Domain\Tracker\PageTracker;
use In2code\Lux\Domain\Tracker\SearchTracker;
use In2code\Lux\Exception\ActionNotAllowedException;
use In2code\Lux\Signal\SignalTrait;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Object\Exception;

/**
 * Class FrontendController
 */
class FrontendController extends ActionController
{
    use SignalTrait;

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
            'redirectRequest'
        ];
        $action = $this->request->getArgument('dispatchAction');
        if (!in_array($action, $allowedActions)) {
            throw new ActionNotAllowedException('Action not allowed', 1518815149);
        }
    }

    /**
     * @param string $dispatchAction
     * @param string $fingerprint
     * @param array $arguments
     * @return void
     * @throws StopActionException
     * @noinspection PhpUnused
     */
    public function dispatchRequestAction(string $dispatchAction, string $fingerprint, array $arguments): void
    {
        $this->forward($dispatchAction, null, null, ['fingerprint' => $fingerprint, 'arguments' => $arguments]);
    }

    /**
     * @param string $fingerprint
     * @param array $arguments
     * @return string
     * @throws Exception
     * @noinspection PhpUnused
     */
    public function pageRequestAction(string $fingerprint, array $arguments): string
    {
        try {
            $visitor = $this->getVisitor($fingerprint);
            $this->callAdditionalTrackers($visitor);
            $pageTracker = $this->objectManager->get(PageTracker::class);
            $pagevisit = $pageTracker->track($visitor, $arguments);
            $newsTracker = $this->objectManager->get(NewsTracker::class);
            $newsTracker->track($visitor, $arguments, $pagevisit);
            $searchTracker = $this->objectManager->get(SearchTracker::class);
            $searchTracker->track($visitor, $arguments);
            return json_encode($this->afterAction($visitor));
        } catch (\Exception $exception) {
            return json_encode($this->getError($exception));
        }
    }

    /**
     * @param string $fingerprint
     * @param array $arguments
     * @return string
     * @throws Exception
     * @noinspection PhpUnused
     */
    public function fieldListeningRequestAction(string $fingerprint, array $arguments): string
    {
        try {
            $visitor = $this->getVisitor($fingerprint);
            /** @noinspection PhpParamsInspection */
            $attributeTracker = $this->objectManager->get(
                AttributeTracker::class,
                $visitor,
                AttributeTracker::CONTEXT_FIELDLISTENING
            );
            $attributeTracker->addAttribute($arguments['key'], $arguments['value']);
            return json_encode($this->afterAction($visitor));
        } catch (\Exception $exception) {
            return json_encode($this->getError($exception));
        }
    }

    /**
     * @param string $fingerprint
     * @param array $arguments
     * @return string
     * @throws Exception
     * @noinspection PhpUnused
     */
    public function formListeningRequestAction(string $fingerprint, array $arguments): string
    {
        try {
            $visitor = $this->getVisitor($fingerprint);
            $values = json_decode($arguments['values'], true);
            /** @noinspection PhpParamsInspection */
            $attributeTracker = $this->objectManager->get(
                AttributeTracker::class,
                $visitor,
                AttributeTracker::CONTEXT_FORMLISTENING
            );
            $attributeTracker->addAttributes($values);
            return json_encode($this->afterAction($visitor));
        } catch (\Exception $exception) {
            return json_encode($this->getError($exception));
        }
    }

    /**
     * @param string $fingerprint
     * @param array $arguments
     * @return string
     * @throws Exception
     * @noinspection PhpUnused
     */
    public function email4LinkRequestAction(string $fingerprint, array $arguments): string
    {
        try {
            $visitor = $this->getVisitor($fingerprint, true);
            /** @noinspection PhpParamsInspection */
            $attributeTracker = $this->objectManager->get(
                AttributeTracker::class,
                $visitor,
                AttributeTracker::CONTEXT_EMAIL4LINK
            );
            $values = json_decode($arguments['values'], true);
            $allowedFields = GeneralUtility::trimExplode(
                ',',
                $this->settings['identification']['email4link']['form']['fields']['enabled'],
                true
            );
            $attributeTracker->addAttributes($values, $allowedFields);

            /** @noinspection PhpParamsInspection */
            $downloadTracker = $this->objectManager->get(DownloadTracker::class, $visitor);
            $downloadTracker->addDownload($arguments['href']);
            if ($arguments['sendEmail'] === 'true') {
                /** @noinspection PhpParamsInspection */
                $this->objectManager->get(SendAssetEmail4LinkService::class, $visitor, $this->settings)
                    ->sendMail($arguments['href']);
            }
            return json_encode($this->afterAction($visitor));
        } catch (\Exception $exception) {
            return json_encode($this->getError($exception));
        }
    }

    /**
     * @param string $fingerprint
     * @param array $arguments
     * @return string
     * @throws Exception
     * @noinspection PhpUnused
     */
    public function downloadRequestAction(string $fingerprint, array $arguments): string
    {
        try {
            $visitor = $this->getVisitor($fingerprint);
            /** @noinspection PhpParamsInspection */
            $downloadTracker = $this->objectManager->get(DownloadTracker::class, $visitor);
            $downloadTracker->addDownload($arguments['href']);
            return json_encode($this->afterAction($visitor));
        } catch (\Exception $exception) {
            return json_encode($this->getError($exception));
        }
    }

    /**
     * @param string $fingerprint
     * @param array $arguments
     * @return string
     * @throws Exception
     * @noinspection PhpUnused
     */
    public function linkClickRequestAction(string $fingerprint, array $arguments): string
    {
        try {
            $visitor = $this->getVisitor($fingerprint);
            /** @noinspection PhpParamsInspection */
            $linkClickTracker = $this->objectManager->get(LinkClickTracker::class, $visitor);
            $linkClickTracker->addLinkClick((int)$arguments['linklistenerIdentifier'], (int)$arguments['pageUid']);
            return json_encode($this->afterAction($visitor));
        } catch (\Exception $exception) {
            return json_encode($this->getError($exception));
        }
    }

    /**
     * @param string $fingerprint empty means no opt-in yet
     * @return string
     * @throws Exception
     */
    public function redirectRequestAction(string $fingerprint): string
    {
        try {
            $visitor = $this->getVisitor($fingerprint);
            return json_encode($this->afterAction($visitor));
        } catch (\Exception $exception) {
            try {
                // Empty fingerprint, create visitor on the fly
                $visitor = new Visitor();
                return json_encode($this->afterAction($visitor));
            } catch (\Exception $exception) {
                return json_encode($this->getError($exception));
            }
        }
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
     * @return void
     */
    protected function callAdditionalTrackers(Visitor $visitor): void
    {
        /** @noinspection PhpParamsInspection */
        $authTracker = $this->objectManager->get(FrontenduserAuthenticationTracker::class, $visitor);
        $authTracker->trackByFrontenduserAuthentication();
        /** @noinspection PhpParamsInspection */
        $luxletterTracker = $this->objectManager->get(
            LuxletterlinkAttributeTracker::class,
            $visitor,
            AttributeTracker::CONTEXT_LUXLETTERLINK
        );
        $luxletterTracker->trackFromLuxletterLink();
    }

    /**
     * This method will be called after normal frontend actions.
     * Pass four parameters to slot. The first is the visitor to use this data. The second is the action name from
     * where the signal came from. The third is an array, which could be returned for passing an array as json to the
     * javascript of the visitor. The last one is mandatory and in this case useless.
     *
     * @param Visitor $visitor
     * @return array
     * @throws Exception
     */
    protected function afterAction(Visitor $visitor): array
    {
        $result = $this->signalDispatch(__CLASS__, 'afterTracking', [$visitor, $this->actionMethodName, [], []]);
        return $result[2];
    }

    /**
     * @param \Exception $exception
     * @return array
     * @throws Exception
     */
    protected function getError(\Exception $exception): array
    {
        $this->signalDispatch(__CLASS__, 'afterTracking', [new Visitor(), 'error', [], ['error' => $exception]]);
        return [
            'error' => true,
            'exception' => [
                'code' => $exception->getCode(),
                'message' => $exception->getMessage()
            ]
        ];
    }

    /**
     * @param string $fingerprint
     * @param bool $tempVisitor
     * @return Visitor
     */
    protected function getVisitor(string $fingerprint, bool $tempVisitor = false): Visitor
    {
        /** @noinspection PhpParamsInspection */
        $visitorFactory = $this->objectManager->get(VisitorFactory::class, $fingerprint, $tempVisitor);
        return $visitorFactory->getVisitor();
    }
}
