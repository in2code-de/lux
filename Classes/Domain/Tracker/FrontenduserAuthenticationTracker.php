<?php
declare(strict_types = 1);
namespace In2code\Lux\Domain\Tracker;

use In2code\Lux\Exception\EmailValidationException;
use In2code\Lux\Utility\FrontendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;
use TYPO3\CMS\Extbase\Domain\Repository\FrontendUserRepository;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException;

/**
 * Class FrontenduserAuthenticationTracker
 */
class FrontenduserAuthenticationTracker extends AbstractFrontenduserTracker
{
    /**
     * @return void
     * @throws EmailValidationException
     * @throws Exception
     * @throws IllegalObjectTypeException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws UnknownObjectException
     */
    public function trackByFrontenduserAuthentication(): void
    {
        if (FrontendUtility::isLoggedInFrontendUser()) {
            $userRepository = GeneralUtility::makeInstance(FrontendUserRepository::class);
            /** @var FrontendUser $user */
            $user = $userRepository->findByUid((int)FrontendUtility::getPropertyFromLoggedInFrontendUser('uid'));

            $this->setRelationToFrontendUser($user);
            $this->addEmailAttribute($user);
        }
    }

    /**
     * @param FrontendUser $user
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    protected function setRelationToFrontendUser(FrontendUser $user): void
    {
        if ($this->visitor->getFrontenduser() === null
            || $user->getUid() !== $this->visitor->getFrontenduser()->getUid()
        ) {
            $this->addOrUpdateRelation($user);
        }
    }

    /**
     * @param FrontendUser $user
     * @return void
     * @throws EmailValidationException
     * @throws Exception
     * @throws IllegalObjectTypeException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws UnknownObjectException
     */
    protected function addEmailAttribute(FrontendUser $user): void
    {
        if ($user->getEmail() !== null) {
            $this->addOrUpdateEmail($user);
        }
    }
}
