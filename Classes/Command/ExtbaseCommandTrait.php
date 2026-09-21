<?php

declare(strict_types=1);

namespace In2code\Lux\Command;

use DateTime;
use In2code\Lux\Domain\Service\SiteService;
use In2code\Lux\Exception\ConfigurationException;
use In2code\Lux\Exception\DateTimeException;
use In2code\Lux\Utility\EnvironmentUtility;
use Symfony\Component\Console\Input\InputOption;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

trait ExtbaseCommandTrait
{
    public const ROOT_PAGE_ID_OPTION_NAME = 'rootPageId';

    protected function configureRootPageIdOption(): void
    {
        $description = 'Root page identifier of the site that holds the TypoScript configuration of lux. ' .
            'Needed in installations with more than one site.';
        $this->addOption(self::ROOT_PAGE_ID_OPTION_NAME, null, InputOption::VALUE_REQUIRED, $description, 0);
    }

    /**
     * @throws ConfigurationException
     */
    public function initializeExtbase(int $rootPageId = 0): void
    {
        if (EnvironmentUtility::isCli() === false) {
            return;
        }
        Bootstrap::initializeBackendAuthentication();
        $siteService = GeneralUtility::makeInstance(SiteService::class);
        $site = $siteService->getDefaultSite();
        if ($rootPageId > 0) {
            $site = $siteService->getSiteFromPageIdentifier($rootPageId);
            if ($site === null) {
                throw new ConfigurationException(
                    'There is no site for page identifier ' . $rootPageId,
                    1790121601
                );
            }
        }
        $request = (new ServerRequest())
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_BE)
            ->withAttribute('site', $site)
            ->withQueryParams(['id' => $site->getRootPageId()]);
        $configurationManager = GeneralUtility::makeInstance(ConfigurationManagerInterface::class);
        $configurationManager->setRequest($request);
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
