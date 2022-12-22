<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Tracker;

use Doctrine\DBAL\DBALException;
use In2code\Lux\Domain\Model\FrontendUser;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Exception\EmailValidationException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;

abstract class AbstractFrontenduserTracker
{
    protected ?Visitor $visitor = null;

    protected string $context = AttributeTracker::CONTEXT_FRONTENDUSER;

    protected int $pageIdentifier = 0;

    public function __construct(
        Visitor $visitor,
        string $context = AttributeTracker::CONTEXT_FRONTENDUSER,
        int $pageIdentifier = 0
    ) {
        $this->visitor = $visitor;
        $this->context = $context;
        $this->pageIdentifier = $pageIdentifier;
    }

    /**
     * @param FrontendUser $user
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    protected function addOrUpdateRelation(FrontendUser $user): void
    {
        $this->visitor->setFrontenduser($user);
        $visitorRepository = GeneralUtility::makeInstance(VisitorRepository::class);
        $visitorRepository->update($this->visitor);
    }

    /**
     * @param FrontendUser $user
     * @return void
     * @throws EmailValidationException
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     * @throws DBALException
     * @throws InvalidConfigurationTypeException
     */
    protected function addOrUpdateEmail(FrontendUser $user): void
    {
        $attributeTracker = GeneralUtility::makeInstance(
            AttributeTracker::class,
            $this->visitor,
            $this->context,
            $this->pageIdentifier
        );
        $attributeTracker->addAttribute('email', $user->getEmail());
    }
}
