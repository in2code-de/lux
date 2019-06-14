<?php
declare(strict_types=1);
namespace In2code\Lux\Controller;

use In2code\Lux\Domain\Factory\VisitorFactory;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Service\SendAssetEmail4LinkService;
use In2code\Lux\Domain\Tracker\AttributeTracker;
use In2code\Lux\Domain\Tracker\DownloadTracker;
use In2code\Lux\Domain\Tracker\PageTracker;
use In2code\Lux\Signal\SignalTrait;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
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
     */
    public function initializeDispatchRequestAction()
    {
        $allowedActions = [
            'pageRequest',
            'fieldListeningRequest',
            'formListeningRequest',
            'email4LinkRequest',
            'downloadRequest'
        ];
        $action = $this->request->getArgument('dispatchAction');
        if (!in_array($action, $allowedActions)) {
            throw new \UnexpectedValueException('Action not allowed', 1518815149);
        }
    }

    /**
     * @param string $dispatchAction
     * @param string $idCookie
     * @param array $arguments
     * @return void
     * @throws StopActionException
     */
    public function dispatchRequestAction(string $dispatchAction, string $idCookie, array $arguments)
    {
        $this->forward($dispatchAction, null, null, ['idCookie' => $idCookie, 'arguments' => $arguments]);
    }

    /**
     * @param string $idCookie
     * @param array $arguments
     * @return string
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws IllegalObjectTypeException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws UnknownObjectException
     */
    public function pageRequestAction(string $idCookie, array $arguments): string
    {
        $visitorFactory = $this->objectManager->get(VisitorFactory::class, $idCookie, $arguments['referrer']);
        $visitor = $visitorFactory->getVisitor();
        $pageTracker = $this->objectManager->get(PageTracker::class);
        $pageTracker->trackPage($visitor, (int)$arguments['pageUid']);
        return json_encode($this->afterAction($visitor));
    }

    /**
     * @param string $idCookie
     * @param array $arguments
     * @return string
     */
    public function fieldListeningRequestAction(string $idCookie, array $arguments): string
    {
        try {
            $visitorFactory = $this->objectManager->get(VisitorFactory::class, $idCookie);
            $visitor = $visitorFactory->getVisitor();
            $attributeTracker = $this->objectManager->get(
                AttributeTracker::class,
                $visitor,
                AttributeTracker::CONTEXT_FIELDLISTENING
            );
            $attributeTracker->addAttribute($arguments['key'], $arguments['value']);
            return json_encode($this->afterAction($visitor));
        } catch (\Exception $exception) {
            return json_encode(['error' => true]);
        }
    }

    /**
     * @param string $idCookie
     * @param array $arguments
     * @return string
     */
    public function formListeningRequestAction(string $idCookie, array $arguments): string
    {
        try {
            $visitorFactory = $this->objectManager->get(VisitorFactory::class, $idCookie);
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
            return json_encode(['error' => true]);
        }
    }

    /**
     * @param string $idCookie
     * @param array $arguments
     * @return string
     */
    public function email4LinkRequestAction(string $idCookie, array $arguments): string
    {
        try {
            $visitorFactory = $this->objectManager->get(VisitorFactory::class, $idCookie);
            $visitor = $visitorFactory->getVisitor();
            $attributeTracker = $this->objectManager->get(
                AttributeTracker::class,
                $visitor,
                AttributeTracker::CONTEXT_EMAIL4LINK
            );
            $attributeTracker->addAttribute('email', $arguments['email']);
            $downloadFactory = $this->objectManager->get(DownloadTracker::class, $visitor);
            $downloadFactory->addDownload($arguments['href']);
            if ($arguments['sendEmail'] === 'true') {
                $this->objectManager->get(SendAssetEmail4LinkService::class, $visitor)->sendMail($arguments['href']);
            }
            return json_encode($this->afterAction($visitor));
        } catch (\Exception $exception) {
            return json_encode(['error' => true]);
        }
    }

    /**
     * @param string $idCookie
     * @param array $arguments
     * @return string
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws IllegalObjectTypeException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws UnknownObjectException
     */
    public function downloadRequestAction(string $idCookie, array $arguments): string
    {
        $visitorFactory = $this->objectManager->get(VisitorFactory::class, $idCookie);
        $visitor = $visitorFactory->getVisitor();
        $downloadFactory = $this->objectManager->get(DownloadTracker::class, $visitor);
        $downloadFactory->addDownload($arguments['href']);
        return json_encode($this->afterAction($visitor));
    }

    /**
     * @return void
     */
    public function trackingOptOutAction()
    {
    }

    /**
     * This method will be called after normal frontend actions.
     * Pass three parameters to slot. The first is the visitor to use this data. The second is the action name from
     * where the signal came from. The third is an array, which could be returned for passing an array as json to the
     * javascript of the visitor.
     *
     * @param Visitor $visitor
     * @return array
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     */
    protected function afterAction(Visitor $visitor): array
    {
        $result = $this->signalDispatch(__CLASS__, 'afterTracking', [$visitor, $this->actionMethodName, []]);
        return $result[2];
    }
}
