<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Service\Email;

use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Service\ConfigurationService;
use In2code\Lux\Events\Log\LogEmail4linkSendEmailEvent;
use In2code\Lux\Events\Log\LogEmail4linkSendEmailFailedEvent;
use In2code\Lux\Events\SetAssetEmail4LinkEvent;
use In2code\Lux\Utility\ObjectUtility;
use In2code\Lux\Utility\StringUtility;
use In2code\Lux\Utility\UrlUtility;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Resource\Security\FileNameValidator;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Fluid\View\StandaloneView;

class SendAssetEmail4LinkService
{
    protected ?Visitor $visitor = null;

    /**
     * TypoScript settings
     *
     * @var array
     */
    protected array $settings = [];

    protected ?ConfigurationService $configurationService = null;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(Visitor $visitor, array $settings)
    {
        $this->visitor = $visitor;
        $this->settings = $settings;
        $this->configurationService = ObjectUtility::getConfigurationService();
        $this->eventDispatcher = GeneralUtility::makeInstance(EventDispatcherInterface::class);
    }

    /**
     * @param string $href
     * @return void
     * @throws InvalidConfigurationTypeException
     */
    public function sendMail(string $href): void
    {
        if ($this->visitor->isNotBlacklisted()) {
            if ($this->isActivatedAndAllowed($href)) {
                $this->send($href);
                $this->eventDispatcher->dispatch(
                    GeneralUtility::makeInstance(LogEmail4linkSendEmailEvent::class, $this->visitor, $href)
                );
            } else {
                $this->eventDispatcher->dispatch(
                    GeneralUtility::makeInstance(LogEmail4linkSendEmailFailedEvent::class, $this->visitor, $href)
                );
            }
        }
    }

    /**
     * @param string $href
     * @return void
     * @throws InvalidConfigurationTypeException
     */
    protected function send(string $href): void
    {
        $message = GeneralUtility::makeInstance(MailMessage::class);
        $message
            ->setTo([$this->visitor->getEmail() => 'Receiver'])
            ->setFrom($this->getSender())
            ->setSubject($this->getSubject())
            ->attachFromPath(GeneralUtility::getFileAbsFileName(UrlUtility::convertToRelative($href)))
            ->html($this->getMailTemplate($href));
        $this->setBcc($message);
        $this->eventDispatcher->dispatch(
            GeneralUtility::makeInstance(SetAssetEmail4LinkEvent::class, $this->visitor, $message, $href)
        );
        $message->send();
    }

    /**
     * @param MailMessage $message
     * @return void
     */
    protected function setBcc(MailMessage $message): void
    {
        if (!empty($this->settings['identification']['email4link']['mail']['bccEmail'])) {
            $bcc = [];
            $emails = GeneralUtility::trimExplode(
                ',',
                $this->settings['identification']['email4link']['mail']['bccEmail'],
                true
            );
            foreach ($emails as $email) {
                if (GeneralUtility::validEmail($email)) {
                    $bcc = array_merge($bcc, [$email => 'Receiver']);
                }
            }
            $message->setBcc($bcc);
        }
    }

    /**
     * @param string $href
     * @return string
     * @throws InvalidConfigurationTypeException
     */
    protected function getMailTemplate(string $href): string
    {
        $mailTemplatePath = $this->configurationService->getTypoScriptSettingsByPath(
            'identification.email4link.mail.mailTemplate'
        );
        $standaloneView = GeneralUtility::makeInstance(StandaloneView::class);
        $standaloneView->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName($mailTemplatePath));
        $standaloneView->assignMultiple([
            'href' => $href,
            'visitor' => $this->visitor,
        ]);
        return $standaloneView->render();
    }

    /**
     * @return array
     * @throws InvalidConfigurationTypeException
     */
    protected function getSender(): array
    {
        $configuration = $this->configurationService->getTypoScriptSettingsByPath('identification.email4link.mail');
        return [$configuration['fromEmail'] => $configuration['fromName']];
    }

    /**
     * @return string
     * @throws InvalidConfigurationTypeException
     */
    protected function getSubject(): string
    {
        return $this->configurationService->getTypoScriptSettingsByPath('identification.email4link.mail.subject');
    }

    /**
     * @param string $href
     * @return bool
     * @throws InvalidConfigurationTypeException
     */
    protected function isActivatedAndAllowed(string $href): bool
    {
        return $this->isEnabled() && $this->isAllowedFileExtension($href) && $this->isAllowedStorage($href)
            && $this->isNotMalicious($href) && $this->isFileExisting($href) && $this->visitor->isIdentified();
    }

    /**
     * @return bool
     * @throws InvalidConfigurationTypeException
     */
    protected function isEnabled(): bool
    {
        $path = 'identification.email4link.mail._enable';
        return $this->configurationService->getTypoScriptSettingsByPath($path) === '1';
    }

    /**
     * @param string $href
     * @return bool
     * @throws InvalidConfigurationTypeException
     */
    protected function isAllowedFileExtension(string $href): bool
    {
        $allowed = false;
        $thisExtension = StringUtility::getExtensionFromPathAndFilename($href);
        $extensionList = $this->configurationService->getTypoScriptSettingsByPath(
            'identification.email4link.mail.allowedFileExtensions'
        );
        $extensions = GeneralUtility::trimExplode(',', $extensionList, true);
        foreach ($extensions as $extension) {
            if (strtolower($extension) === strtolower($thisExtension)) {
                $allowed = true;
                break;
            }
        }
        return $allowed;
    }

    /**
     * @param string $href
     * @return bool
     */
    protected function isAllowedStorage(string $href): bool
    {
        $allowed = false;
        $storageRepository = GeneralUtility::makeInstance(StorageRepository::class);
        $storages = $storageRepository->findAll();
        foreach ($storages as $storage) {
            if ($storage->isOnline()) {
                $configuration = $storage->getConfiguration();
                $basePath = $configuration['basePath'];
                if (StringUtility::startsWith(UrlUtility::convertToRelative($href), $basePath)) {
                    $allowed = true;
                    break;
                }
            }
        }
        return $allowed;
    }

    /**
     * @param string $href
     * @return bool
     */
    protected function isNotMalicious(string $href): bool
    {
        return GeneralUtility::makeInstance(FileNameValidator::class)->isValid($href)
            && GeneralUtility::validPathStr($href);
    }

    /**
     * @param string $href
     * @return bool
     */
    protected function isFileExisting(string $href): bool
    {
        return file_exists(GeneralUtility::getFileAbsFileName(UrlUtility::convertToRelative($href)));
    }
}
