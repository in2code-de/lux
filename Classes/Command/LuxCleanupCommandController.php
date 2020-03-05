<?php
declare(strict_types=1);
namespace In2code\Lux\Command;

use Doctrine\DBAL\DBALException;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Repository\VisitorRepository;
use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;

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
     * @throws InvalidQueryException
     */
    public function removeUnknownVisitorsByAgeCommand(int $timestamp)
    {
        $visitors = $this->visitorRepository->findByLastChangeUnknown($timestamp);
        /** @var Visitor $visitor */
        foreach ($visitors as $visitor) {
            $this->visitorRepository->removeRelatedTableRowsByVisitorUid($visitor);
            $this->visitorRepository->removeVisitorByVisitorUid($visitor);
        }
        $this->outputLine(count($visitors) . ' successfully removed');
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
     * @throws InvalidQueryException
     */
    public function removeVisitorsByAgeCommand(int $timestamp)
    {
        $visitors = $this->visitorRepository->findByLastChange($timestamp);
        /** @var Visitor $visitor */
        foreach ($visitors as $visitor) {
            $this->visitorRepository->removeRelatedTableRowsByVisitorUid($visitor);
            $this->visitorRepository->removeVisitorByVisitorUid($visitor);
        }
        $this->outputLine(count($visitors) . ' successfully removed');
    }

    /**
     * Remove all visitors!
     *
     *      Remove all visitors
     *      !!! Really removes visitors and all rows from related tables from the database
     *
     * @return void
     * @cli
     * @throws DBALException
     */
    public function removeAllVisitorsCommand()
    {
        $this->visitorRepository->truncateAll();
        $this->outputLine('Every lux table successfully truncated');
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
        /** @var Visitor $visitor */
        $visitor = $this->visitorRepository->findByUid($visitorUid);
        $this->visitorRepository->removeRelatedTableRowsByVisitorUid($visitor);
        $this->visitorRepository->removeVisitorByVisitorUid($visitor);
    }

    /**
     * Remove a visitor by a given property. E.g. removing all google bots with
     * "./vendor/bin/typo3cms luxcleanup:removevisitorbyproperty userAgent Googlebot 0" or
     * "./vendor/bin/typo3cms luxcleanup:removevisitorbyproperty fingerprints.userAgent Googlebot 0"
     *
     *      Remove a visitor by a given property. E.g. removing all
     *      !!! Really removes visitors and all rows from related tables from the database
     *
     * @param string $propertyName
     * @param string $propertyValue
     * @param bool $exactMatch
     * @return void
     * @throws DBALException
     * @throws InvalidQueryException
     */
    public function removeVisitorByPropertyCommand(string $propertyName, string $propertyValue, bool $exactMatch = true)
    {
        $visitors = $this->visitorRepository->findAllByProperty($propertyName, $propertyValue, $exactMatch);
        /** @var Visitor $visitor */
        foreach ($visitors as $visitor) {
            $this->visitorRepository->removeRelatedTableRowsByVisitorUid($visitor);
            $this->visitorRepository->removeVisitorByVisitorUid($visitor);
        }
        $this->outputLine(count($visitors) . ' successfully removed');
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
