<?php

declare(strict_types=1);

namespace In2code\Lux\Tests\Unit\Fixtures\Domain\Service\Email;

use In2code\Lux\Domain\Service\Email\SendSummaryService;
use In2code\Lux\Exception\ConfigurationException;
use In2code\Lux\Exception\EmailValidationException;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;

class SendSummaryServiceFixture extends SendSummaryService
{
    /**
     * @throws ConfigurationException
     * @throws InvalidConfigurationTypeException
     */
    public function getSummaryMailConfigurationPublic(): array
    {
        return $this->getSummaryMailConfiguration();
    }

    /**
     * @throws ConfigurationException
     * @throws InvalidConfigurationTypeException
     */
    public function getSenderPublic(): array
    {
        return $this->getSender();
    }

    /**
     * @throws ConfigurationException
     * @throws InvalidConfigurationTypeException
     */
    public function getSubjectPublic(): string
    {
        return $this->getSubject();
    }

    /**
     * @throws ConfigurationException
     * @throws InvalidConfigurationTypeException
     */
    public function getMailTemplatePublic(array $assignment = []): string
    {
        return $this->getMailTemplate($assignment);
    }

    /**
     * @throws ConfigurationException
     * @throws EmailValidationException
     */
    public function checkPropertiesPublic(array $emails): void
    {
        $this->checkProperties($emails);
    }
}
