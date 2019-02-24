<?php
declare(strict_types=1);
namespace In2code\Lux\Command;

use Doctrine\DBAL\DBALException;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Repository\VisitorRepository;
use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;

/**
 * Class LuxCleanupCommandController
 */
class LuxCleanupCommandController extends CommandController
{

    /**
     * @var VisitorRepository
     */
    protected $visitorRepository = null;

    /**
     * Remove all unknown visitors where the last update is older than a given timestamp
     *
     *      Remove all unknown visitors where the last update (tstamp) is older than a given timestamp
     *      !!! Really removes visitors and all rows from related tables from the database
     *
     * @param int $timestamp
     * @return void
     * @throws DBALException
     */
    public function removeUnknownVisitorsByAgeCommand(int $timestamp)
    {
        $visitorRepository = $this->objectManager->get(VisitorRepository::class);
        $visitors = $visitorRepository->findByLastChangeUnknown($timestamp);
        /** @var Visitor $visitor */
        foreach ($visitors as $visitor) {
            $this->visitorRepository->removeRelatedTableRowsByVisitorUid($visitor->getUid());
            $this->visitorRepository->removeVisitorByVisitorUid($visitor->getUid());
        }
    }

    /**
     * Remove all visitors where the last update is older than a given timestamp
     *
     *      Remove all visitors where the last update (tstamp) is older than a given timestamp
     *      !!! Really removes visitors and all rows from related tables from the database
     *
     * @param int $timestamp
     * @return void
     * @throws DBALException
     */
    public function removeVisitorsByAgeCommand(int $timestamp)
    {
        $visitorRepository = $this->objectManager->get(VisitorRepository::class);
        $visitors = $visitorRepository->findByLastChange($timestamp);
        /** @var Visitor $visitor */
        foreach ($visitors as $visitor) {
            $this->visitorRepository->removeRelatedTableRowsByVisitorUid($visitor->getUid());
            $this->visitorRepository->removeVisitorByVisitorUid($visitor->getUid());
        }
    }

    /**
     * Remove a visitor by a given UID
     *
     *      Remove a single visitor by a given UID
     *      !!! Really removes visitors and all rows from related tables from the database
     *
     * @param int $visitorUid
     * @return void
     * @throws DBALException
     */
    public function removeVisitorByUidCommand(int $visitorUid)
    {
        $this->visitorRepository->removeRelatedTableRowsByVisitorUid($visitorUid);
        $this->visitorRepository->removeVisitorByVisitorUid($visitorUid);
    }

    /**
     * @param VisitorRepository $visitorRepository
     * @return void
     */
    public function injectVisitorRepository(VisitorRepository $visitorRepository)
    {
        $this->visitorRepository = $visitorRepository;
    }
}
