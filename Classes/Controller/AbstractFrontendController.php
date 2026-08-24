<?php

declare(strict_types=1);
namespace In2code\Lux\Controller;

use In2code\Lux\Exception\DisallowedUserAgentException;
use In2code\Lux\Exception\FingerprintMustNotBeEmptyException;
use In2code\Lux\Exception\RateLimitException;
use In2code\Lux\Exception\Validation\IdentificatorFormatException;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Http\PropagateResponseException;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

abstract class AbstractFrontendController extends ActionController
{
    protected const STATUS_BAD_REQUEST = 400;
    protected const STATUS_FORBIDDEN = 403;
    protected const STATUS_TOO_MANY_REQUESTS = 429;
    protected const STATUS_INTERNAL_SERVER_ERROR = 500;
    protected const EXCEPTION_STATUS_MAP = [
        FingerprintMustNotBeEmptyException::class => self::STATUS_BAD_REQUEST,
        IdentificatorFormatException::class => self::STATUS_BAD_REQUEST,
        DisallowedUserAgentException::class => self::STATUS_FORBIDDEN,
        RateLimitException::class => self::STATUS_TOO_MANY_REQUESTS,
    ];
    protected const HEADERS_ROBOTS = ['X-Robots-Tag' => 'noindex, nofollow'];

    protected function assertRequiredArguments(array $argumentNames): void
    {
        foreach ($argumentNames as $argumentName) {
            if ($this->request->hasArgument($argumentName) === false) {
                $this->propagateClientError('Required argument "' . $argumentName . '" is not set', 1787577001);
            }
        }
    }

    protected function assertNotEmptyArguments(array $argumentNames): void
    {
        $this->assertRequiredArguments($argumentNames);
        foreach ($argumentNames as $argumentName) {
            $value = $this->request->getArgument($argumentName);
            if ($value === '' || $value === []) {
                $this->propagateClientError('Required argument "' . $argumentName . '" is empty', 1787577002);
            }
        }
    }

    protected function propagateClientError(string $message, int $code): never
    {
        throw new PropagateResponseException(
            $this->getErrorResponse($message, $code, self::STATUS_BAD_REQUEST),
            1787577003
        );
    }

    protected function getStatus(Throwable $exception): int
    {
        foreach (self::EXCEPTION_STATUS_MAP as $exceptionClassName => $status) {
            if ($exception instanceof $exceptionClassName) {
                return $status;
            }
        }
        return self::STATUS_INTERNAL_SERVER_ERROR;
    }

    protected function getErrorResponse(string $message, int $code, int $status): ResponseInterface
    {
        return new JsonResponse(
            [
                'error' => [
                    'message' => $message,
                    'code' => $code,
                ],
            ],
            $status,
            self::HEADERS_ROBOTS
        );
    }
}
