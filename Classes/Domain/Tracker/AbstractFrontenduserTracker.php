<?php
declare(strict_types = 1);
namespace In2code\Lux\Domain\Tracker;

use Doctrine\DBAL\DBALException;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Exception\EmailValidationException;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException;

/**
 * Class AbstractFrontenduserTracker
 */
abstract class AbstractFrontenduserTracker
{
    /**
     * @var Visitor
     */
    protected $visitor = null;

    /**
     * @var string
     */
    protected $context = AttributeTracker::CONTEXT_FRONTENDUSER;

    /**
     * @var int
     */
    protected $pageIdentifier = 0;

    /**
     * AbstractFrontenduserTracker constructor.
     * @param Visitor $visitor
     * @param string $context
     * @param int $pageIdentifier
     */
    public function __construct(
        Visitor $visitor,
        string $context = '',
        int $pageIdentifier = 0
    ) {
        $this->visitor = $visitor;
        if ($context !== '') {
            $this->context = $context;
        }
        $this->pageIdentifier = $pageIdentifier;
    }

    /**
     * @param FrontendUser $user
     * @return void
     * @throws Exception
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    protected function addOrUpdateRelation(FrontendUser $user): void
    {
        $this->visitor->setFrontenduser($user);
        $visitorRepository = ObjectUtility::getObjectManager()->get(VisitorRepository::class);
        $visitorRepository->update($this->visitor);
    }

    /**
     * @param FrontendUser $user
     * @return void
     * @throws DBALException
     * @throws EmailValidationException
     * @throws Exception
     * @throws IllegalObjectTypeException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws UnknownObjectException
     */
    protected function addOrUpdateEmail(FrontendUser $user): void
    {
        $attributeTracker = ObjectUtility::getObjectManager()->get(
            AttributeTracker::class,
            $this->visitor,
            $this->context,
            $this->pageIdentifier
        );
        $attributeTracker->addAttribute('email', $user->getEmail());
    }
}
