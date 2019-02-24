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
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;

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
     */
    public function pageRequestAction(string $idCookie, array $arguments): string
    {
        $visitorFactory = $this->objectManager->get(VisitorFactory::class, $idCookie, $arguments['referrer']);
        $pageTracker = $this->objectManager->get(PageTracker::class);
        $visitor = $visitorFactory->getVisitor();
        $pageTracker->trackPage($visitor, (int)$arguments['pageUid']);
        return json_encode($this->afterTracking($visitor));
    }

    /**
     * @param string $idCookie
     * @param array $arguments
     * @return string
     */
    public function fieldListeningRequestAction(string $idCookie, array $arguments): string
    {
        $visitorFactory = $this->objectManager->get(VisitorFactory::class, $idCookie);
        $visitor = $visitorFactory->getVisitor();
        $attributeTracker = $this->objectManager->get(
            AttributeTracker::class,
            $visitor,
            AttributeTracker::CONTEXT_FIELDLISTENING
        );
        $attributeTracker->addAttribute($arguments['key'], $arguments['value']);
        return json_encode($this->afterTracking($visitor));
    }

    /**
     * @param string $idCookie
     * @param array $arguments
     * @return string
     */
    public function email4LinkRequestAction(string $idCookie, array $arguments): string
    {
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
        return json_encode($this->afterTracking($visitor));
    }

    /**
     * @param string $idCookie
     * @param array $arguments
     * @return string
     */
    public function downloadRequestAction(string $idCookie, array $arguments): string
    {
        $visitorFactory = $this->objectManager->get(VisitorFactory::class, $idCookie);
        $visitor = $visitorFactory->getVisitor();
        $downloadFactory = $this->objectManager->get(DownloadTracker::class, $visitor);
        $downloadFactory->addDownload($arguments['href']);
        return json_encode($this->afterTracking($visitor));
    }

    /**
     * @return void
     */
    public function trackingOptOutAction()
    {
    }

    /**
     * Pass three parameters to slot. The first is the visitor to use this data. The second is the action name from
     * where the signal came from. The third is an array, which could be returned for passing an array as json to the
     * javascript of the visitor.
     *
     * @param Visitor $visitor
     * @return array
     */
    protected function afterTracking(Visitor $visitor): array
    {
        $result = $this->signalDispatch(__CLASS__, __FUNCTION__, [$visitor, $this->actionMethodName, []]);
        return $result[2];
    }
}
