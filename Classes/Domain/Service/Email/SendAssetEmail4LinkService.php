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
use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Mail\FluidEmail;
use TYPO3\CMS\Core\Mail\MailerInterface;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\Security\FileNameValidator;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;

class SendAssetEmail4LinkService
{
    protected ?Visitor $visitor = null;
    protected ?File $file = null;
    protected string $href = '';

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

    public function sendMail(string $href, ?File $file): void
    {
        $this->href = $href;
        $this->file = $file;
        if ($this->visitor->isNotBlacklisted()) {
            if ($this->isActivatedAndAllowed()) {
                $this->send();
                $this->eventDispatcher->dispatch(new LogEmail4linkSendEmailEvent($this->visitor, $this->href));
            } else {
                $this->eventDispatcher->dispatch(new LogEmail4linkSendEmailFailedEvent($this->visitor, $this->href));
            }
        }
    }

    protected function send(): void
    {
        /** @var SetAssetEmail4LinkEvent $event */
        $event = $this->eventDispatcher->dispatch(
            new SetAssetEmail4LinkEvent($this->visitor, $this->href, $this->file)
        );
        $mail = GeneralUtility::makeInstance(FluidEmail::class);
        $mail
            ->to($this->visitor->getEmail())
            ->from($this->getSender())
            ->format(FluidEmail::FORMAT_BOTH)
            ->setTemplate('Email4Link')
            ->assignMultiple([
                'href' => $event->getHref(),
                'visitor' => $event->getVisitor(),
                'file' => $event->getFile(),
                'request' => $this->getRequest(),
            ])
            ->attachFromPath(GeneralUtility::getFileAbsFileName(UrlUtility::convertToRelative($event->getHref())));
        $this->setBcc($mail);
        GeneralUtility::makeInstance(MailerInterface::class)->send($mail);
    }

    protected function setBcc(FluidEmail $mail): void
    {
        if (($this->settings['identification']['email4link']['mail']['bccEmail'] ?? '') !== '') {
            $bcc = [];
            $emails = GeneralUtility::trimExplode(
                ',',
                $this->settings['identification']['email4link']['mail']['bccEmail'],
                true
            );
            foreach ($emails as $email) {
                if (GeneralUtility::validEmail($email)) {
                    $bcc[] = new Address($email);
                }
            }
            $mail->bcc(...$bcc);
        }
    }

    protected function getSender(): Address
    {
        $configuration = $this->configurationService->getTypoScriptSettingsByPath('identification.email4link.mail');
        return new Address($configuration['fromEmail'], $configuration['fromName']);
    }

    protected function isActivatedAndAllowed(): bool
    {
        return $this->isEnabled()
            && $this->isAllowedFileExtension()
            && $this->isAllowedStorage()
            && $this->isNotMalicious()
            && $this->isFileExisting()
            && $this->visitor->isIdentified();
    }

    protected function isEnabled(): bool
    {
        $path = 'identification.email4link.mail._enable';
        return $this->configurationService->getTypoScriptSettingsByPath($path) === '1';
    }

    protected function isAllowedFileExtension(): bool
    {
        $allowed = false;
        $thisExtension = StringUtility::getExtensionFromPathAndFilename($this->href);
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

    protected function isAllowedStorage(): bool
    {
        $allowed = false;
        $storageRepository = GeneralUtility::makeInstance(StorageRepository::class);
        $storages = $storageRepository->findAll();
        foreach ($storages as $storage) {
            if ($storage->isOnline()) {
                $configuration = $storage->getConfiguration();
                $basePath = $configuration['basePath'];
                if (StringUtility::startsWith(UrlUtility::convertToRelative($this->href), $basePath)) {
                    $allowed = true;
                    break;
                }
            }
        }
        return $allowed;
    }

    protected function isNotMalicious(): bool
    {
        return GeneralUtility::makeInstance(FileNameValidator::class)->isValid($this->href)
            && GeneralUtility::validPathStr($this->href);
    }

    protected function isFileExisting(): bool
    {
        return file_exists(GeneralUtility::getFileAbsFileName(UrlUtility::convertToRelative($this->href)));
    }

    protected function getRequest(): ?ServerRequest
    {
        return $GLOBALS['TYPO3_REQUEST'] ?? null;
    }
}
