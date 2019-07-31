<?php
declare(strict_types=1);
namespace In2code\Lux\Slot;

use In2code\Lux\Domain\Model\Attribute;
use In2code\Lux\Domain\Model\Download;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Service\LogService;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;

/**
 * Class Log
 */
class Log implements SingletonInterface
{

    /**
     * @var LogService|null
     */
    protected $logService = null;

    /**
     * @param LogService $logService
     * @return void
     */
    public function injectFormRepository(LogService $logService)
    {
        $this->logService = $logService;
    }

    /**
     * @param Visitor $visitor
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function logNewVisitor(Visitor $visitor)
    {
        $this->logService->logNewVisitor($visitor);
    }

    /**
     * @param Attribute $attribute
     * @param Visitor $visitor
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function logIdentifiedVisitor(Attribute $attribute, Visitor $visitor)
    {
        $this->logService->logIdentifiedVisitor($visitor);
    }

    /**
     * @param Attribute $attribute
     * @param Visitor $visitor
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function logIdentifiedVisitorByFormListening(Attribute $attribute, Visitor $visitor)
    {
        $this->logService->logIdentifiedVisitorFormListening($visitor);
    }

    /**
     * @param Attribute $attribute
     * @param Visitor $visitor
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function logIdentifiedVisitorByEmail4Link(Attribute $attribute, Visitor $visitor)
    {
        $this->logService->logIdentifiedVisitorByEmail4Link($visitor);
    }

    /**
     * @param Attribute $attribute
     * @param Visitor $visitor
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function logIdentifiedVisitorByLuxletterlink(Attribute $attribute, Visitor $visitor)
    {
        $this->logService->logIdentifiedVisitorByLuxletterlink($visitor);
    }

    /**
     * @param Attribute $attribute
     * @param Visitor $visitor
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function logIdentifiedVisitorByFrontendauthentication(Attribute $attribute, Visitor $visitor)
    {
        $this->logService->logIdentifiedVisitorByFrontendauthentication($visitor);
    }

    /**
     * @param Visitor $visitor
     * @param string $href
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function logEmail4LinkEmail(Visitor $visitor, string $href)
    {
        $this->logService->logEmail4LinkEmail($visitor, $href);
    }

    /**
     * @param Visitor $visitor
     * @param string $href
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function logEmail4LinkEmailFailed(Visitor $visitor, string $href)
    {
        $this->logService->logEmail4LinkEmailFailed($visitor, $href);
    }

    /**
     * @param Download $download
     * @param Visitor $visitor
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function logDownload(Download $download, Visitor $visitor)
    {
        $this->logService->logDownload($download);
    }
}
