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

    /**
     * @param array $visitors
     */
    public function __construct(array $visitors)
    {
        $this->visitors = $visitors;
        $this->configurationService = ObjectUtility::getConfigurationService();
    }

    /**
     * @param array $emails
     * @return bool
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
     * @return array
     * @throws ConfigurationException
     * @throws InvalidConfigurationTypeException
     */
    protected function getSender(): array
    {
        $configuration = $this->getSummaryMailConfiguration();
        return [$configuration['fromEmail'] => $configuration['fromName']];
    }

    /**
     * @return string
     * @throws ConfigurationException
     * @throws InvalidConfigurationTypeException
     */
    protected function getSubject(): string
    {
        return $this->getSummaryMailConfiguration()['subject'] ?? '';
    }

    /**
     * ConfigurationService::getTypoScriptSettingsByPath() returns an empty string if the path could not be
     * resolved. Accessing that string with an array offset leads to a TypeError that does not tell the
     * integrator anything about the actual problem, so fail with a speaking exception instead.
     *
     * @return array
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
     * @param array $assignment
     * @return string
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
     * @param array $emails
     * @return void
     * @throws EmailValidationException
     * @throws ConfigurationException
     */
    protected function checkProperties(array $emails)
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
