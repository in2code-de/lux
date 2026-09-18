<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Service\Email;

use In2code\Lux\Domain\Service\ConfigurationService;
use In2code\Lux\Exception\ConfigurationException;
use In2code\Lux\Exception\EmailValidationException;
use In2code\Lux\Utility\EmailUtility;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Core\Mail\MailerInterface;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;

class SendSummaryService
{
    protected string $luxLogoPath = 'EXT:lux/Resources/Public/Icons/lux.png';

    protected array $visitors;
    protected ?ConfigurationService $configurationService = null;

    public function __construct(array $visitors)
    {
        $this->visitors = $visitors;
        $this->configurationService = ObjectUtility::getConfigurationService();
    }

    /**
     * @throws ConfigurationException
     * @throws EmailValidationException
     * @throws InvalidConfigurationTypeException
     */
    public function send(array $emails): bool
    {
        $this->checkProperties($emails);
        $message = GeneralUtility::makeInstance(MailMessage::class);
        $message->embedFromPath(GeneralUtility::getFileAbsFileName($this->luxLogoPath), 'luxLogo');
        $message
            ->setTo(EmailUtility::extendEmailReceiverArray($emails))
            ->setFrom($this->getSender())
            ->setSubject($this->getSubject())
            ->html($this->getMailTemplate());
        GeneralUtility::makeInstance(MailerInterface::class)->send($message);
        return true;
    }

    /**
     * @throws ConfigurationException
     * @throws InvalidConfigurationTypeException
     */
    protected function getSender(): array
    {
        $configuration = $this->getSummaryMailConfiguration();
        return [$configuration['fromEmail'] => $configuration['fromName']];
    }

    /**
     * @throws ConfigurationException
     * @throws InvalidConfigurationTypeException
     */
    protected function getSubject(): string
    {
        return $this->getSummaryMailConfiguration()['subject'] ?? '';
    }

    /**
     * @throws ConfigurationException
     * @throws InvalidConfigurationTypeException
     */
    protected function getSummaryMailConfiguration(): array
    {
        $configuration = $this->configurationService->getTypoScriptSettingsByPath('commandControllers.summaryMail');
        if (is_array($configuration) === false) {
            throw new ConfigurationException(
                'TypoScript setting plugin.tx_lux_fe.settings.commandControllers.summaryMail could not be '
                . 'resolved. Please add the static TypoScript of EXT:lux to the TypoScript template of the '
                . 'site that is used by this command.',
                1789652586
            );
        }
        return $configuration;
    }

    /**
     * @throws ConfigurationException
     * @throws InvalidConfigurationTypeException
     */
    protected function getMailTemplate(array $assignment = []): string
    {
        $mailTemplatePath = $this->getSummaryMailConfiguration()['mailTemplate'] ?? '';
        $view = GeneralUtility::makeInstance(ViewFactoryInterface::class)->create(new ViewFactoryData(
            templatePathAndFilename: GeneralUtility::getFileAbsFileName($mailTemplatePath),
        ));
        $view->assignMultiple(['visitors' => $this->visitors] + $assignment);
        return $view->render();
    }

    /**
     * @throws EmailValidationException
     * @throws ConfigurationException
     */
    protected function checkProperties(array $emails): void
    {
        if ($emails === []) {
            throw new ConfigurationException('No emails to send given', 1524299754);
        }
        foreach ($emails as $email) {
            if (GeneralUtility::validEmail($email) === false) {
                throw new EmailValidationException('Wrong email format given', 1524299869);
            }
        }
        if (count($this->visitors) === 0) {
            throw new ConfigurationException('No leads given to send email to', 1524300114);
        }
    }
}
