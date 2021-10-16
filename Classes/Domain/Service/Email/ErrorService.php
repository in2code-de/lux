<?php
declare(strict_types = 1);
namespace In2code\Lux\Domain\Service\Email;

use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Service\ConfigurationService;
use In2code\Lux\Utility\ObjectUtility;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use TYPO3\CMS\Core\Mail\FluidEmail;
use TYPO3\CMS\Core\Mail\Mailer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ErrorService
 */
class ErrorService
{
    /**
     * @var ConfigurationService
     */
    protected $configurationService = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->configurationService = ObjectUtility::getConfigurationService();
    }

    /**
     * @param string $emails commaseparated email list
     * @param \Exception $exception
     * @param Visitor $visitor
     * @return void
     * @throws TransportExceptionInterface
     */
    public function send(string $emails, \Exception $exception, Visitor $visitor): void
    {
        foreach (GeneralUtility::trimExplode(',', $emails, true) as $email) {
            if (GeneralUtility::validEmail($email)) {
                $this->sendEmail($email, $exception, $visitor);
            }
        }
    }

    /**
     * @param string $email
     * @param \Exception $exception
     * @param Visitor $visitor
     * @return void
     * @throws TransportExceptionInterface
     */
    protected function sendEmail(string $email, \Exception $exception, Visitor $visitor): void
    {
        $message = 'Message: ' . $exception->getMessage() . ' (' . $exception->getCode() . ')';
        $message .= ' ----------------------------------------------------------------------------------------------- ';
        $message .= 'Source: ' . $exception->getFile() . ':' . $exception->getLine();
        $message .= ' ----------------------------------------------------------------------------------------------- ';
        $message .= 'Visitor: ' . $visitor->getFullName() . ' (' . $visitor->getUid() . ')';
        $message .= ' ----------------------------------------------------------------------------------------------- ';
        $message .= 'Request: ' . print_r($_REQUEST, true);
        /** @var FluidEmail $email */
        $email = GeneralUtility::makeInstance(FluidEmail::class)
            ->to($email)
            ->subject('lux failure')
            ->setTemplate('Default')
            ->assignMultiple([
                'headline' => 'lux failure',
                'introduction' => 'Exception catched in frontend request.',
                'content' => $message
            ]);
        GeneralUtility::makeInstance(Mailer::class)->send($email);
    }
}
