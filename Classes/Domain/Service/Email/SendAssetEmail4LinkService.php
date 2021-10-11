<?php
declare(strict_types = 1);
namespace In2code\Lux\Domain\Service\Email;

use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Service\ConfigurationService;
use In2code\Lux\Signal\SignalTrait;
use In2code\Lux\Utility\ObjectUtility;
use In2code\Lux\Utility\StringUtility;
use In2code\Lux\Utility\UrlUtility;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Resource\Security\FileNameValidator;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\Exception;
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
     * TypoScript settings
     *
     * @var array
     */
    protected $settings = [];

    /**
     * @var ConfigurationService|null
     */
    protected $configurationService = null;

    /**
     * SendAssetEmail4LinkService constructor.
     *
     * @param Visitor $visitor
     * @param array $settings
     * @throws Exception
     */
    public function __construct(Visitor $visitor, array $settings)
    {
        $this->visitor = $visitor;
        $this->settings = $settings;
        $this->configurationService = ObjectUtility::getConfigurationService();
    }

    /**
     * @param string $href
     * @return void
     * @throws Exception
     */
    public function sendMail(string $href): void
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
     * @throws Exception
     */
    protected function send(string $href): void
    {
        $message = ObjectUtility::getObjectManager()->get(MailMessage::class);
        $message
            ->setTo([$this->visitor->getEmail() => 'Receiver'])
            ->setFrom($this->getSender())
            ->setSubject($this->getSubject())
            ->attachFromPath(GeneralUtility::getFileAbsFileName(UrlUtility::convertToRelative($href)))
            ->html($this->getMailTemplate($href));
        $this->setBcc($message);
        $this->signalDispatch(__CLASS__, 'send', [$message, $this->visitor, $href]);
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
     * @throws Exception
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
     * @throws Exception
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
        $path = 'identification.email4link.mail._enable';
        return $this->configurationService->getTypoScriptSettingsByPath($path) === '1';
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
     * @throws Exception
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
