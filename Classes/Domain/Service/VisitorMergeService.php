<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Service;

use Doctrine\DBAL\Driver\Exception as ExceptionDbalDriver;
use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Domain\Model\Attribute;
use In2code\Lux\Domain\Model\Categoryscoring;
use In2code\Lux\Domain\Model\Download;
use In2code\Lux\Domain\Model\Fingerprint;
use In2code\Lux\Domain\Model\Linkclick;
use In2code\Lux\Domain\Model\Log;
use In2code\Lux\Domain\Model\Pagevisit;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Repository\AttributeRepository;
use In2code\Lux\Domain\Repository\FingerprintRepository;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Events\VisitorsMergeEvent;
use In2code\Lux\Exception\ConfigurationException;
use In2code\Lux\Utility\DatabaseUtility;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * Merge duplicated visitors to only one visitor. Merge duplicates in these situations:
 * - If more than only one visitor with the same fingerprint are existing
 * - If visitor has a new fingerprint but tells the system a known email address, we have to move all attributes and
 * pagevisits to the existing visitor and add the new fingerprint
 *
 * Class VisitorMergeService
 */
class VisitorMergeService
{
    protected ?Visitor $firstVisitor = null;
    protected VisitorRepository $visitorRepository;
    protected FingerprintRepository $fingerprintRepository;
    protected AttributeRepository $attributeRepository;
    protected LogService $logService;
    protected EventDispatcherInterface $eventDispatcher;

    public function __construct(
        VisitorRepository $visitorRepository,
        FingerprintRepository $fingerprintRepository,
        AttributeRepository $attributeRepository,
        LogService $logService,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->visitorRepository = $visitorRepository;
        $this->fingerprintRepository = $fingerprintRepository;
        $this->attributeRepository = $attributeRepository;
        $this->logService = $logService;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param string $identificator
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     * @throws ExceptionDbalDriver
     * @throws ConfigurationException
     * @throws ExceptionDbal
     */
    public function mergeByFingerprint(string $identificator): void
    {
        if ($this->fingerprintRepository->getFingerprintCountByValue($identificator) > 0) {
            $visitors = $this->visitorRepository->findDuplicatesByFingerprint($identificator);
            if ($visitors->count() > 1) {
                $this->logService->logVisitorMergeByFingerprint($visitors);
                $this->merge($visitors);
            }
        }
    }

    /**
     * @param string $email
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     * @throws ConfigurationException
     * @throws ExceptionDbal
     */
    public function mergeByEmail(string $email): void
    {
        $visitors = $this->visitorRepository->findDuplicatesByEmail($email);
        if ($visitors->count() > 1) {
            $this->logService->logVisitorMergeByEmail($visitors);
            $this->merge($visitors);
        }
    }

    /**
     * @param QueryResultInterface $visitors
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     * @throws ConfigurationException
     * @throws ExceptionDbal
     */
    protected function merge(QueryResultInterface $visitors): void
    {
        foreach ($visitors as $visitor) {
            $this->setFirstVisitor($visitor);
            if ($visitor !== $this->getFirstVisitor()) {
                $this->mergePagevisits($visitor);
                $this->mergeLogs($visitor);
                $this->mergeCategoryscorings($visitor);
                $this->mergeDownloads($visitor);
                $this->mergeLinkclicks($visitor);
                $this->mergeShortenervisits($visitor);
                $this->mergeAttributes($visitor);
                $this->updateFingerprints($visitor);
                $this->deleteVisitor($visitor);
            }
        }
        $this->eventDispatcher->dispatch(
            GeneralUtility::makeInstance(VisitorsMergeEvent::class, $visitors)
        );
    }

    /**
     * Update existing pagevisits with another parent visitor uid
     *
     * @param Visitor $newVisitor
     * @return void
     * @throws ConfigurationException
     * @throws ExceptionDbal
     */
    protected function mergePagevisits(Visitor $newVisitor): void
    {
        $connection = DatabaseUtility::getConnectionForTable(Pagevisit::TABLE_NAME);
        $connection->executeQuery(
            'update ' . Pagevisit::TABLE_NAME . ' set visitor = ' . (int)$this->getFirstVisitor()->getUid() . ' ' .
            'where visitor = ' . (int)$newVisitor->getUid()
        );
    }

    /**
     * Update existing logs with another parent visitor uid
     *
     * @param Visitor $newVisitor
     * @return void
     * @throws ConfigurationException
     * @throws ExceptionDbal
     */
    protected function mergeLogs(Visitor $newVisitor): void
    {
        $connection = DatabaseUtility::getConnectionForTable(Log::TABLE_NAME);
        $connection->executeQuery(
            'update ' . Log::TABLE_NAME . ' set visitor = ' . (int)$this->getFirstVisitor()->getUid() . ' ' .
            'where visitor = ' . (int)$newVisitor->getUid()
        );
    }

    /**
     * Update existing categoryscorings with another parent visitor uid
     *
     * @param Visitor $newVisitor
     * @return void
     * @throws ConfigurationException
     * @throws ExceptionDbal
     */
    protected function mergeCategoryscorings(Visitor $newVisitor): void
    {
        /** @var Categoryscoring $categoryscoring */
        foreach ($newVisitor->getCategoryscorings() as $categoryscoring) {
            $category = $categoryscoring->getCategory();
            $existingCs = $this->getFirstVisitor()->getCategoryscoringByCategory($category);
            $connection = DatabaseUtility::getConnectionForTable(Categoryscoring::TABLE_NAME);
            if ($existingCs !== null) {
                $this->getFirstVisitor()->increaseCategoryscoringByCategory($categoryscoring->getScoring(), $category);
                $connection->executeQuery(
                    'update ' . Categoryscoring::TABLE_NAME . ' set deleted = 1' .
                    ' where visitor = ' . (int)$newVisitor->getUid() . ' and category = ' . (int)$category->getUid()
                );
            } else {
                $connection->executeQuery(
                    'update ' . Categoryscoring::TABLE_NAME .
                    ' set visitor = ' . (int)$this->getFirstVisitor()->getUid() .
                    ' where visitor = ' . (int)$newVisitor->getUid() . ' and category = ' . (int)$category->getUid()
                );
            }
        }
    }

    /**
     * Update existing downloads with another parent visitor uid
     *
     * @param Visitor $newVisitor
     * @return void
     * @throws ConfigurationException
     * @throws ExceptionDbal
     */
    protected function mergeDownloads(Visitor $newVisitor): void
    {
        $connection = DatabaseUtility::getConnectionForTable(Download::TABLE_NAME);
        $connection->executeQuery(
            'update ' . Download::TABLE_NAME . ' set visitor = ' . (int)$this->getFirstVisitor()->getUid() . ' ' .
            'where visitor = ' . (int)$newVisitor->getUid()
        );
    }

    /**
     * Update existing linkclicks with another parent visitor uid
     *
     * @param Visitor $newVisitor
     * @return void
     * @throws ConfigurationException
     * @throws ExceptionDbal
     */
    protected function mergeLinkclicks(Visitor $newVisitor): void
    {
        $connection = DatabaseUtility::getConnectionForTable(Linkclick::TABLE_NAME);
        $connection->executeQuery(
            'update ' . Linkclick::TABLE_NAME . ' set visitor = ' . (int)$this->getFirstVisitor()->getUid() . ' ' .
            'where visitor = ' . (int)$newVisitor->getUid()
        );
    }

    /**
     * Update existing shortenervisits with another parent visitor uid (if luxenterprise is installed)
     *
     * @param Visitor $newVisitor
     * @return void
     * @throws ConfigurationException
     * @throws ExceptionDbal
     */
    protected function mergeShortenervisits(Visitor $newVisitor): void
    {
        if (DatabaseUtility::isTableExisting('tx_luxenterprise_domain_model_shortenervisit')) {
            $connection = DatabaseUtility::getConnectionForTable('tx_luxenterprise_domain_model_shortenervisit');
            $connection->executeQuery(
                'update tx_luxenterprise_domain_model_shortenervisit ' .
                'set visitor = ' . (int)$this->getFirstVisitor()->getUid() . ' ' .
                'where visitor = ' . (int)$newVisitor->getUid()
            );
        }
    }

    /**
     * Update existing attributes with another parent visitor uid
     *
     * @param Visitor $newVisitor
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     * @throws ConfigurationException
     * @throws ExceptionDbal
     */
    protected function mergeAttributes(Visitor $newVisitor): void
    {
        foreach ($newVisitor->getAttributes() as $newAttribute) {
            $attribute = $this->attributeRepository->findByVisitorAndKey(
                $this->getFirstVisitor(),
                $newAttribute->getName()
            );
            if ($attribute !== null) {
                $attribute->setValue($newAttribute->getValue());
                $this->attributeRepository->update($attribute);
                $this->attributeRepository->remove($newAttribute);
                $this->attributeRepository->persistAll();
            } else {
                $connection = DatabaseUtility::getConnectionForTable(Attribute::TABLE_NAME);
                $connection->executeQuery(
                    'update ' . Attribute::TABLE_NAME . ' set visitor = ' . $this->getFirstVisitor()->getUid() . ' ' .
                    'where uid = ' . (int)$newAttribute->getUid()
                );
            }
        }
    }

    /**
     * @param Visitor $newVisitor
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     * @throws ConfigurationException
     * @throws ExceptionDbal
     */
    protected function updateFingerprints(Visitor $newVisitor): void
    {
        foreach ($newVisitor->getFingerprints() as $fingerprint) {
            if (in_array($fingerprint->getValue(), $this->getFirstVisitor()->getFingerprintValues())) {
                $newVisitor->removeFingerprint($fingerprint);
                $this->deleteFingerprint($fingerprint);
            } else {
                $this->getFirstVisitor()->addFingerprint($fingerprint);
            }
        }
        $this->visitorRepository->update($this->getFirstVisitor());
        $this->visitorRepository->persistAll();
    }

    /**
     * @param Fingerprint $fingerprint
     * @return void
     * @throws ExceptionDbal
     */
    protected function deleteFingerprint(Fingerprint $fingerprint): void
    {
        $connection = DatabaseUtility::getConnectionForTable(Fingerprint::TABLE_NAME);
        $connection->executeQuery(
            'update ' . Fingerprint::TABLE_NAME . ' set deleted=1 where uid=' . $fingerprint->getUid()
        );
    }

    /**
     * @param Visitor $newVisitor
     * @return void
     * @throws ExceptionDbal
     */
    protected function deleteVisitor(Visitor $newVisitor): void
    {
        $connection = DatabaseUtility::getConnectionForTable(Visitor::TABLE_NAME);
        $connection->executeQuery(
            'update ' . Visitor::TABLE_NAME . ' set deleted=1 where uid=' . (int)$newVisitor->getUid()
        );
    }

    /**
     * @return Visitor
     * @throws ConfigurationException
     */
    protected function getFirstVisitor(): Visitor
    {
        if ($this->firstVisitor === null) {
            throw new ConfigurationException('first visitor must not be null by logic', 1679691026);
        }
        return $this->firstVisitor;
    }

    protected function setFirstVisitor(Visitor $visitor): void
    {
        if ($this->firstVisitor === null) {
            $this->firstVisitor = $visitor;
        }
    }
}
