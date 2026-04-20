<?php

declare(strict_types=1);
namespace In2code\Lux\Command;

use DateTime;
use In2code\Lux\Domain\Service\SiteService;
use In2code\Lux\Exception\DateTimeException;
use In2code\Lux\Utility\EnvironmentUtility;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

trait ExtbaseCommandTrait
{
    public function initializeExtbase(): void
    {
        if (EnvironmentUtility::isCli()) {
            Bootstrap::initializeBackendAuthentication();
            $site = GeneralUtility::makeInstance(SiteService::class)->getDefaultSite();
            $request = (new ServerRequest())
                ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_BE)
                ->withAttribute('site', $site)
                ->withQueryParams(['id' => $site->getRootPageId()]);
            $configurationManager = GeneralUtility::makeInstance(ConfigurationManagerInterface::class);
            $configurationManager->setRequest($request);
        }
    }

    protected function parseTime(string $timeString): DateTime
    {
        if (MathUtility::canBeInterpretedAsInteger($timeString)) {
            return DateTime::createFromFormat('U', $timeString);
        }
        try {
            return new DateTime($timeString);
        } catch (\Throwable $exception) {
            throw new DateTimeException('Could not parse time: ' . $timeString, 1773128558, $exception);
        }
    }
}
