<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\Service\Email;

use In2code\Lux\Domain\Service\ConfigurationService;
use In2code\Lux\Exception\ConfigurationException;
use In2code\Lux\Exception\EmailValidationException;
use In2code\Lux\Utility\ConfigurationUtility;
use In2code\Lux\Utility\EmailUtility;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Class SendSummaryService
 */
class SendSummaryService
{
    /**
     * @var string
     */
    protected $luxLogoPath = 'EXT:lux/Resources/Public/Icons/lux.png';

    /**
     * @var QueryResultInterface|array
     */
    protected $visitors = null;

    /**
     * @var ConfigurationService
     */
    protected $configurationService = null;

    /**
     * SendSummaryService constructor.
     *
     * @param QueryResultInterface|array $visitors
     * @throws Exception
     */
    public function __construct($visitors)
    {
        $this->visitors = $visitors;
        $this->configurationService = ObjectUtility::getConfigurationService();
    }

    /**
     * @param array $emails
     * @return bool
     * @throws ConfigurationException
     * @throws EmailValidationException
     * @throws Exception
     */
    public function send(array $emails): bool
    {
        $this->checkProperties($emails);
        $message = ObjectUtility::getObjectManager()->get(MailMessage::class);
        if (ConfigurationUtility::isVersionToCompareSameOrLowerThenCurrentTypo3Version('10.0.0')) {
            // TYPO3 10
            $message->embedFromPath(GeneralUtility::getFileAbsFileName($this->luxLogoPath), 'luxLogo');
            $message
                ->setTo(EmailUtility::extendEmailReceiverArray($emails))
                ->setFrom($this->getSender())
                ->setSubject($this->getSubject())
                ->html($this->getMailTemplate());
        } else {
            // Todo: Remove when TYPO3 9.5 support will be dropped
            $logo = $message->embed(\Swift_Image::fromPath(GeneralUtility::getFileAbsFileName($this->luxLogoPath)));
            $message
                ->setTo(EmailUtility::extendEmailReceiverArray($emails))
                ->setFrom($this->getSender())
                ->setSubject($this->getSubject())
                ->setBody($this->getMailTemplate(['luxLogo' => $logo]), 'text/html');
        }
        $message->send();
        return $message->isSent();
    }

    /**
     * @return array
     */
    protected function getSender(): array
    {
        $configuration = $this->configurationService->getTypoScriptSettingsByPath('commandControllers.summaryMail');
        return [$configuration['fromEmail'] => $configuration['fromName']];
    }

    /**
     * @return string
     */
    protected function getSubject(): string
    {
        return $this->configurationService->getTypoScriptSettingsByPath('commandControllers.summaryMail.subject');
    }

    /**
     * @param array $assignment
     * @return string
     * @throws Exception
     */
    protected function getMailTemplate(array $assignment = []): string
    {
        $mailTemplatePath = $this->configurationService->getTypoScriptSettingsByPath(
            'commandControllers.summaryMail.mailTemplate'
        );
        $standaloneView = ObjectUtility::getObjectManager()->get(StandaloneView::class);
        $standaloneView->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName($mailTemplatePath));
        $standaloneView->assignMultiple([
            'visitors' => $this->visitors
        ]);
        $standaloneView->assignMultiple($assignment);
        return $standaloneView->render();
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
