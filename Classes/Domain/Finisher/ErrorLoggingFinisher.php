<?php
declare(strict_types = 1);
namespace In2code\Lux\Domain\Finisher;

use Exception;
use In2code\Lux\Domain\Service\Email\ErrorService;
use In2code\Lux\Domain\Service\LogService;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;

/**
 * Class ErrorLoggingFinisher
 */
class ErrorLoggingFinisher extends AbstractFinisher
{
    /**
     * @var string[]
     */
    protected $startWithControllerActions = [
        'error'
    ];

    /**
     * @return bool
     */
    public function shouldFinisherRun(): bool
    {
        return $this->getConfigurationByKey('enable') === '1'
            && isset($this->parameters['error'])
            && is_a($this->parameters['error'], Exception::class)
            && $this->isExceptionAllowed($this->parameters['error']);
    }

    /**
     * @return array
     * @throws IllegalObjectTypeException
     * @throws TransportExceptionInterface
     * @throws UnknownObjectException
     */
    public function start(): array
    {
        /** @var Exception $exception */
        $exception = $this->event->getArgument('error');
        $this->log($exception);
        $this->sendEmail($exception);
        return [];
    }

    /**
     * @param Exception $exception
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    protected function log(Exception $exception): void
    {
        /** @var LogService $logService */
        $logService = GeneralUtility::makeInstance(LogService::class);
        $logService->logError(
            $this->visitor,
            $exception->getMessage(),
            $exception->getCode(),
            $exception->getFile() . ':' . $exception->getLine()
        );
    }

    /**
     * @param Exception $exception
     * @return void
     * @throws TransportExceptionInterface
     */
    protected function sendEmail(Exception $exception): void
    {
        $emails = $this->getConfigurationByPath('notification.emails');
        if (!empty($emails)) {
            /** @var ErrorService $errorService */
            $errorService = GeneralUtility::makeInstance(ErrorService::class);
            $errorService->send($emails, $exception, $this->visitor);
        }
    }

    /**
     * Filter not allowed exceptions
     *
     * @param Exception $exception
     * @return bool
     */
    protected function isExceptionAllowed(Exception $exception): bool
    {
        $disabledList = $this->getConfigurationByPath('disabledExceptions');
        foreach (GeneralUtility::trimExplode(',', $disabledList, true) as $disabled) {
            if (is_a($exception, $disabled)) {
                return false;
            }
        }
        return true;
    }
}
