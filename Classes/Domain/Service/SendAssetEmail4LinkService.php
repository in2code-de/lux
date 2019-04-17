<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\Service;

use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Signal\SignalTrait;
use In2code\Lux\Utility\ObjectUtility;
use In2code\Lux\Utility\StringUtility;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Class SendAssetEmail4LinkService
 */
class SendAssetEmail4LinkService
{
    use SignalTrait;

    /**
     * @var Visitor|null
     */
    protected $visitor = null;

    /**
     * @var ConfigurationService|null
     */
    protected $configurationService = null;

    /**
     * SendAssetEmail4LinkService constructor.
     *
     * @param Visitor $visitor
     */
    public function __construct(Visitor $visitor)
    {
        $this->visitor = $visitor;
        $this->configurationService = ObjectUtility::getConfigurationService();
    }

    /**
     * @param string $href
     * @return void
     */
    public function sendMail(string $href)
    {
        if ($this->visitor->isNotBlacklisted()) {
            if ($this->isActivatedAndAllowed($href)) {
                $this->send($href);
                $this->signalDispatch(__CLASS__, 'email4linkSendEmail', [$this->visitor, $href]);
            } else {
                $this->signalDispatch(__CLASS__, 'email4linkSendEmailFailed', [$this->visitor, $href]);
            }
        }
    }

    /**
     * @param string $href
     * @return void
     */
    protected function send(string $href)
    {
        $message = ObjectUtility::getObjectManager()->get(MailMessage::class);
        $message
            ->setTo([$this->visitor->getEmail() => 'Receiver'])
            ->setFrom($this->getSender())
            ->setSubject($this->getSubject())
            ->attach(\Swift_Attachment::fromPath(GeneralUtility::getFileAbsFileName($this->cleanHref($href))))
            ->setBody($this->getMailTemplate($href), 'text/html');
        $this->signalDispatch(__CLASS__, 'send', [$message, $this->visitor, $href]);
        $message->send();
    }

    /**
     * @param string $href
     * @return string
     */
    protected function getMailTemplate(string $href): string
    {
        $mailTemplatePath = $this->configurationService->getTypoScriptSettingsByPath(
            'identification.email4link.mail.mailTemplate'
        );
        $standaloneView = ObjectUtility::getObjectManager()->get(StandaloneView::class);
        $standaloneView->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName($mailTemplatePath));
        $standaloneView->assignMultiple([
            'href' => $href,
            'visitor' => $this->visitor
        ]);
        return $standaloneView->render();
    }

    /**
     * @return array
     */
    protected function getSender(): array
    {
        $configuration = $this->configurationService->getTypoScriptSettingsByPath('identification.email4link.mail');
        return [$configuration['fromEmail'] => $configuration['fromName']];
    }

    /**
     * @return string
     */
    protected function getSubject(): string
    {
        return $this->configurationService->getTypoScriptSettingsByPath('identification.email4link.mail.subject');
    }

    /**
     * @param string $href
     * @return bool
     */
    protected function isActivatedAndAllowed(string $href): bool
    {
        return $this->isEnabled() && $this->isAllowedFileExtension($href) && $this->isAllowedStorage($href)
            && $this->isNotMalicious($href) && $this->isFileExisting($href) && $this->visitor->isIdentified();
    }

    /**
     * @return bool
     */
    protected function isEnabled(): bool
    {
        return $this->configurationService->getTypoScriptSettingsByPath('identification.email4link.mail._enable')
            === '1';
    }

    /**
     * @param string $href
     * @return bool
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
        $storageRepository = ObjectUtility::getObjectManager()->get(StorageRepository::class);
        $storages = $storageRepository->findAll();
        foreach ($storages as $storage) {
            if ($storage->isOnline()) {
                $configuration = $storage->getConfiguration();
                $basePath = $configuration['basePath'];
                if (StringUtility::startsWith($this->cleanHref($href), $basePath)) {
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
        return GeneralUtility::verifyFilenameAgainstDenyPattern($href)
            && GeneralUtility::validPathStr($href);
    }

    /**
     * @param string $href
     * @return bool
     */
    protected function isFileExisting(string $href): bool
    {
        return file_exists(GeneralUtility::getFileAbsFileName($this->cleanHref($href)));
    }

    /**
     * Remove leading slash or domain from href for comparing with basePath
     *
     * @param string $path
     * @return string
     */
    protected function cleanHref(string $path): string
    {
        $path = ltrim($path, StringUtility::getCurrentUri());
        $path = ltrim($path, '/');
        return $path;
    }
}
