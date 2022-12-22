<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\LogEventListeners;

use In2code\Lux\Domain\Service\LogService;
use In2code\Lux\Events\Log\DownloadEvent;
use In2code\Lux\Events\Log\LinkClickEvent;
use In2code\Lux\Events\Log\LogEmail4linkSendEmailEvent;
use In2code\Lux\Events\Log\LogEmail4linkSendEmailFailedEvent;
use In2code\Lux\Events\Log\LogVisitorEvent;
use In2code\Lux\Events\Log\LogVisitorIdentifiedByEmail4linkEvent;
use In2code\Lux\Events\Log\LogVisitorIdentifiedByFieldlisteningEvent;
use In2code\Lux\Events\Log\LogVisitorIdentifiedByFormlisteningEvent;
use In2code\Lux\Events\Log\LogVisitorIdentifiedByFrontendauthenticationEvent;
use In2code\Lux\Events\Log\LogVisitorIdentifiedByLuxletterlinkEvent;
use In2code\Lux\Events\Log\SearchEvent;
use In2code\Lux\Events\Log\UtmEvent;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;

class Log implements SingletonInterface
{
    protected ?LogService $logService = null;

    public function injectFormRepository(LogService $logService): void
    {
        $this->logService = $logService;
    }

    /**
     * @param LogVisitorEvent $logVisitorEvent
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function logNewVisitor(LogVisitorEvent $logVisitorEvent): void
    {
        $this->logService->logNewVisitor($logVisitorEvent->getVisitor());
    }

    /**
     * @param LogVisitorIdentifiedByFieldlisteningEvent $event
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function logIdentifiedVisitorByFieldListening(LogVisitorIdentifiedByFieldlisteningEvent $event): void
    {
        $this->logService->logIdentifiedVisitor($event->getVisitor(), $event->getPageIdentifier());
    }

    /**
     * @param LogVisitorIdentifiedByFormlisteningEvent $event
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function logIdentifiedVisitorByFormListening(LogVisitorIdentifiedByFormlisteningEvent $event): void
    {
        $this->logService->logIdentifiedVisitorFormListening($event->getVisitor(), $event->getPageIdentifier());
    }

    /**
     * @param LogVisitorIdentifiedByEmail4linkEvent $event
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function logIdentifiedVisitorByEmail4Link(LogVisitorIdentifiedByEmail4linkEvent $event): void
    {
        $this->logService->logIdentifiedVisitorByEmail4Link($event->getVisitor(), $event->getPageIdentifier());
    }

    /**
     * @param LogVisitorIdentifiedByLuxletterlinkEvent $event
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function logIdentifiedVisitorByLuxletterlink(LogVisitorIdentifiedByLuxletterlinkEvent $event): void
    {
        $this->logService->logIdentifiedVisitorByLuxletterlink($event->getVisitor(), $event->getPageIdentifier());
    }

    /**
     * @param LogVisitorIdentifiedByFrontendauthenticationEvent $event
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function logIdentifiedVisitorByFrontendauthentication(
        LogVisitorIdentifiedByFrontendauthenticationEvent $event
    ): void {
        $this->logService->logIdentifiedVisitorByFrontendauthentication(
            $event->getVisitor(),
            $event->getPageIdentifier()
        );
    }

    /**
     * @param LogEmail4linkSendEmailEvent $event
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function logEmail4LinkEmail(LogEmail4linkSendEmailEvent $event): void
    {
        $this->logService->logEmail4LinkEmail($event->getVisitor(), $event->getHref());
    }

    /**
     * @param LogEmail4linkSendEmailFailedEvent $event
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function logEmail4LinkEmailFailed(LogEmail4linkSendEmailFailedEvent $event): void
    {
        $this->logService->logEmail4LinkEmailFailed($event->getVisitor(), $event->getHref());
    }

    /**
     * @param DownloadEvent $event
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function logDownload(DownloadEvent $event): void
    {
        $this->logService->logDownload($event->getDownload());
    }

    /**
     * @param UtmEvent $event
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function logUtm(UtmEvent $event): void
    {
        $this->logService->logUtm($event->getUtm());
    }

    /**
     * @param SearchEvent $event
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function logSearch(SearchEvent $event): void
    {
        $this->logService->logSearch($event->getVisitor(), $event->getSearchUid());
    }

    /**
     * @param LinkClickEvent $event
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function logLinkClick(LinkClickEvent $event): void
    {
        $this->logService->logLinkListener($event->getVisitor(), $event->getLinklistener(), $event->getPageUid());
    }
}
