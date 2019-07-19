<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\Tracker;

use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Utility\FrontendUtility;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;
use TYPO3\CMS\Extbase\Domain\Repository\FrontendUserRepository;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException;

/**
 * Class FrontenduserAttributeTracker
 */
class FrontenduserAttributeTracker
{

    /**
     * @var Visitor
     */
    protected $visitor = null;

    /**
     * FrontenduserAttributeTracker constructor.
     * @param Visitor $visitor
     */
    public function __construct(Visitor $visitor)
    {
        $this->visitor = $visitor;
    }

    /**
     * @return void
     * @throws IllegalObjectTypeException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws UnknownObjectException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function trackByFrontenduserAuthentication()
    {
        if (FrontendUtility::isLoggedInFrontendUser()) {
            $this->setRelationToFrontendUser();
            $this->addAttributesFromFrontendUser();
        }
    }

    /**
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    protected function setRelationToFrontendUser()
    {
        if ($this->visitor->getFrontenduser() === null
            || (int)FrontendUtility::getPropertyFromLoggedInFrontendUser('uid')
            !== $this->visitor->getFrontenduser()->getUid()
        ) {
            $userRepository = ObjectUtility::getObjectManager()->get(FrontendUserRepository::class);
            /** @var FrontendUser $user */
            $user = $userRepository->findByUid((int)FrontendUtility::getPropertyFromLoggedInFrontendUser('uid'));
            $this->visitor->setFrontenduser($user);
            $visitorRepository = ObjectUtility::getObjectManager()->get(VisitorRepository::class);
            $visitorRepository->update($this->visitor);
        }
    }

    /**
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     * @throws \Doctrine\DBAL\DBALException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     */
    protected function addAttributesFromFrontendUser()
    {
        if (FrontendUtility::getPropertyFromLoggedInFrontendUser('email') !== null) {
            $attributeTracker = ObjectUtility::getObjectManager()->get(
                AttributeTracker::class,
                $this->visitor,
                AttributeTracker::CONTEXT_FRONTENDUSER
            );
            $attributeTracker->addAttribute('email', FrontendUtility::getPropertyFromLoggedInFrontendUser('email'));
        }
    }
}
