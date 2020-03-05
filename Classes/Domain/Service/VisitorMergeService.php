<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\Service;

use Doctrine\DBAL\DBALException;
use In2code\Lux\Domain\Model\Attribute;
use In2code\Lux\Domain\Model\Categoryscoring;
use In2code\Lux\Domain\Model\Download;
use In2code\Lux\Domain\Model\Log;
use In2code\Lux\Domain\Model\Pagevisit;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Repository\AttributeRepository;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Signal\SignalTrait;
use In2code\Lux\Utility\DatabaseUtility;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException;

/**
 * If visitor has a new fingerprint but tells the system a known email address, we have to move all attributes and
 * pagevisits to the existing visitor and add the new fingerprint
 *
 * Class VisitorMergeService
 */
class VisitorMergeService
{
    use SignalTrait;

    /**
     * @var string
     */
    protected $email = '';

    /**
     * @var Visitor|null
     */
    protected $firstVisitor = null;

    /**
     * @var VisitorRepository|null
     */
    protected $visitorRepository = null;

    /**
     * @var AttributeRepository|null
     */
    protected $attributeRepository = null;

    /**
     * VisitorMergeService constructor.
     *
     * @param string $email
     */
    public function __construct(string $email)
    {
        $this->email = $email;
        $this->visitorRepository = ObjectUtility::getObjectManager()->get(VisitorRepository::class);
        $this->attributeRepository = ObjectUtility::getObjectManager()->get(AttributeRepository::class);
    }

    /**
     * @return void
     * @throws DBALException
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     */
    public function merge()
    {
        $visitors = $this->visitorRepository->findDuplicatesByEmail($this->email);
        /** @var QueryResultInterface $visitors */
        if ($visitors->count() > 1) {
            foreach ($visitors as $visitor) {
                $this->setFirstVisitor($visitor);
                if ($visitor !== $this->firstVisitor) {
                    $this->mergePagevisits($visitor);
                    $this->mergeLogs($visitor);
                    $this->mergeCategoryscorings($visitor);
                    $this->mergeDownloads($visitor);
                    $this->mergeAttributes($visitor);
                    $this->updateFingerprints($visitor);
                    $this->deleteVisitor($visitor);
                }
            }
            $this->signalDispatch(__CLASS__, 'mergeVisitors', [$visitors]);
        }
    }

    /**
     * Update existing pagevisits with another parent visitor uid
     *
     * @param Visitor $newVisitor
     * @return void
     * @throws DBALException
     */
    protected function mergePagevisits(Visitor $newVisitor)
    {
        $connection = DatabaseUtility::getConnectionForTable(Pagevisit::TABLE_NAME);
        $connection->query(
            'update ' . Pagevisit::TABLE_NAME . ' set visitor = ' . (int)$this->firstVisitor->getUid() . ' ' .
            'where visitor = ' . (int)$newVisitor->getUid()
        )->execute();
    }

    /**
     * Update existing logs with another parent visitor uid
     *
     * @param Visitor $newVisitor
     * @return void
     * @throws DBALException
     */
    protected function mergeLogs(Visitor $newVisitor)
    {
        $connection = DatabaseUtility::getConnectionForTable(Log::TABLE_NAME);
        $connection->query(
            'update ' . Log::TABLE_NAME . ' set visitor = ' . (int)$this->firstVisitor->getUid() . ' ' .
            'where visitor = ' . (int)$newVisitor->getUid()
        )->execute();
    }

    /**
     * Update existing categoryscorings with another parent visitor uid
     *
     * @param Visitor $newVisitor
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     * @throws DBALException
     */
    protected function mergeCategoryscorings(Visitor $newVisitor)
    {
        /** @var Categoryscoring $categoryscoring */
        foreach ($newVisitor->getCategoryscorings() as $categoryscoring) {
            $category = $categoryscoring->getCategory();
            $existingCs = $this->firstVisitor->getCategoryscoringByCategory($category);
            $connection = DatabaseUtility::getConnectionForTable(Categoryscoring::TABLE_NAME);
            if ($existingCs !== null) {
                $this->firstVisitor->increaseCategoryscoringByCategory($categoryscoring->getScoring(), $category);
                $connection->query(
                    'update ' . Categoryscoring::TABLE_NAME . ' set deleted = 1' .
                    ' where visitor = ' . (int)$newVisitor->getUid() . ' and category = ' . (int)$category->getUid()
                )->execute();
            } else {
                $connection->query(
                    'update ' . Categoryscoring::TABLE_NAME . ' set visitor = ' . (int)$this->firstVisitor->getUid() .
                    ' where visitor = ' . (int)$newVisitor->getUid() . ' and category = ' . (int)$category->getUid()
                )->execute();
            }
        }
    }

    /**
     * Update existing downloads with another parent visitor uid
     *
     * @param Visitor $newVisitor
     * @return void
     * @throws DBALException
     */
    protected function mergeDownloads(Visitor $newVisitor)
    {
        $connection = DatabaseUtility::getConnectionForTable(Download::TABLE_NAME);
        $connection->query(
            'update ' . Download::TABLE_NAME . ' set visitor = ' . (int)$this->firstVisitor->getUid() . ' ' .
            'where visitor = ' . (int)$newVisitor->getUid()
        )->execute();
    }

    /**
     * Update existing attributes with another parent visitor uid
     *
     * @param Visitor $newVisitor
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     * @throws DBALException
     */
    protected function mergeAttributes(Visitor $newVisitor)
    {
        foreach ($newVisitor->getAttributes() as $newAttribute) {
            $attribute = $this->attributeRepository->findByVisitorAndKey($this->firstVisitor, $newAttribute->getName());
            if ($attribute !== null) {
                $attribute->setValue($newAttribute->getValue());
                $this->attributeRepository->update($attribute);
                $this->attributeRepository->remove($newAttribute);
                $this->attributeRepository->persistAll();
            } else {
                $connection = DatabaseUtility::getConnectionForTable(Attribute::TABLE_NAME);
                $connection->query(
                    'update ' . Attribute::TABLE_NAME . ' set visitor = ' . $this->firstVisitor->getUid() . ' ' .
                    'where uid = ' . (int)$newAttribute->getUid()
                )->execute();
            }
        }
    }

    /**
     * @param Visitor $newVisitor
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    protected function updateFingerprints(Visitor $newVisitor)
    {
        $this->firstVisitor->addFingerprints($newVisitor->getFingerprints());
        $this->visitorRepository->update($this->firstVisitor);
        $this->visitorRepository->persistAll();
    }

    /**
     * @param Visitor $newVisitor
     * @return void
     * @throws DBALException
     */
    protected function deleteVisitor(Visitor $newVisitor)
    {
        $connection = DatabaseUtility::getConnectionForTable(Visitor::TABLE_NAME);
        $connection
            ->query('update ' . Visitor::TABLE_NAME . ' set deleted=1 where uid=' . (int)$newVisitor->getUid())
            ->execute();
    }

    /**
     * @param Visitor $visitor
     * @return void
     */
    protected function setFirstVisitor(Visitor $visitor)
    {
        if ($this->firstVisitor === null) {
            $this->firstVisitor = $visitor;
        }
    }
}
