<?php
declare(strict_types=1);
namespace In2code\Lux\Controller;

use Doctrine\DBAL\DBALException;
use In2code\Lux\Domain\Factory\VisitorFactory;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Service\SendAssetEmail4LinkService;
use In2code\Lux\Domain\Tracker\AttributeTracker;
use In2code\Lux\Domain\Tracker\DownloadTracker;
use In2code\Lux\Domain\Tracker\FrontenduserAuthenticationTracker;
use In2code\Lux\Domain\Tracker\LinkListenerTracker;
use In2code\Lux\Domain\Tracker\LuxletterlinkAttributeTracker;
use In2code\Lux\Domain\Tracker\PageTracker;
use In2code\Lux\Exception\ActionNotAllowedException;
use In2code\Lux\Exception\EmailValidationException;
use In2code\Lux\Signal\SignalTrait;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException;

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
            'linkListenerRequest'
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
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @noinspection PhpUnused
     */
    public function pageRequestAction(string $fingerprint, array $arguments): string
    {
        try {
            $visitorFactory = $this->objectManager->get(VisitorFactory::class, $fingerprint);
            $visitor = $visitorFactory->getVisitor();
            $this->callAdditionalTrackers($visitor);
            $pageTracker = $this->objectManager->get(PageTracker::class);
            $pageTracker->trackPage($visitor, (int)$arguments['pageUid'], $arguments['referrer']);
            return json_encode($this->afterAction($visitor));
        } catch (\Exception $exception) {
            return json_encode($this->getError($exception));
        }
    }

    /**
     * @param string $fingerprint
     * @param array $arguments
     * @return string
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @noinspection PhpUnused
     */
    public function fieldListeningRequestAction(string $fingerprint, array $arguments): string
    {
        try {
            $visitorFactory = $this->objectManager->get(VisitorFactory::class, $fingerprint);
            $visitor = $visitorFactory->getVisitor();
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
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @noinspection PhpUnused
     */
    public function formListeningRequestAction(string $fingerprint, array $arguments): string
    {
        try {
            $visitorFactory = $this->objectManager->get(VisitorFactory::class, $fingerprint);
            $visitor = $visitorFactory->getVisitor();
            $values = json_decode($arguments['values'], true);
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
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @noinspection PhpUnused
     */
    public function email4LinkRequestAction(string $fingerprint, array $arguments): string
    {
        try {
            $visitorFactory = $this->objectManager->get(VisitorFactory::class, $fingerprint);
            $visitor = $visitorFactory->getVisitor();
            $attributeTracker = $this->objectManager->get(
                AttributeTracker::class,
                $visitor,
                AttributeTracker::CONTEXT_EMAIL4LINK
            );
            $attributeTracker->addAttribute('email', $arguments['email']);
            $downloadTracker = $this->objectManager->get(DownloadTracker::class, $visitor);
            $downloadTracker->addDownload($arguments['href']);
            if ($arguments['sendEmail'] === 'true') {
                $this->objectManager->get(SendAssetEmail4LinkService::class, $visitor)->sendMail($arguments['href']);
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
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @noinspection PhpUnused
     */
    public function downloadRequestAction(string $fingerprint, array $arguments): string
    {
        try {
            $visitorFactory = $this->objectManager->get(VisitorFactory::class, $fingerprint);
            $visitor = $visitorFactory->getVisitor();
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
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @noinspection PhpUnused
     */
    public function linkListenerRequestAction(string $fingerprint, array $arguments): string
    {
        try {
            $visitorFactory = $this->objectManager->get(VisitorFactory::class, $fingerprint);
            $visitor = $visitorFactory->getVisitor();
            $linkListenerTracker = $this->objectManager->get(LinkListenerTracker::class, $visitor);
            $linkListenerTracker->addLinkClick($arguments['tag'], (int)$arguments['pageUid']);
            return json_encode($this->afterAction($visitor));
        } catch (\Exception $exception) {
            return json_encode($this->getError($exception));
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
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws DBALException
     * @throws EmailValidationException
     * @throws Exception
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    protected function callAdditionalTrackers(Visitor $visitor): void
    {
        $authenticationTracker = $this->objectManager->get(FrontenduserAuthenticationTracker::class, $visitor);
        $authenticationTracker->trackByFrontenduserAuthentication();
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
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     */
    protected function afterAction(Visitor $visitor): array
    {
        $result = $this->signalDispatch(__CLASS__, 'afterTracking', [$visitor, $this->actionMethodName, [], []]);
        return $result[2];
    }

    /**
     * @param \Exception $exception
     * @return array
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
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
}
