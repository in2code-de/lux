<?php

declare(strict_types=1);

namespace In2code\Lux\Domain\Cache;

use In2code\Lux\Backend\Units\UnitFinder;
use In2code\Lux\Exception\ConfigurationException;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class CacheWarmup
{
    protected UnitFinder $unitFinder;

    protected array $routesAndPaths = [
        'lux_LuxAnalysis' => [
            'AnalysisDashboardPagevisits',
            'AnalysisDashboardDownloads',
//            'AnalysisDashboardPagevisitsleads', // would need BackendAuthentication
            'AnalysisDashboardBrowser',
            'AnalysisDashboardTopPages',
            'AnalysisDashboardTopDownloads',
            'AnalysisDashboardTopNews',
            'AnalysisDashboardTopSearch',
            'AnalysisDashboardSocialmedia',
        ],
        'lux_LuxLead' => [
            'LeadDashboardRecurring',
//            'LeadDashboardHottest', // can not be cached CLI
            'LeadDashboardIdentified',
            'LeadDashboardIdentifiedpermonth',
            'LeadDashboardIdentifiedpermethod',
            'LeadDashboardReferrer',
            'LeadDashboardMap',
            'LeadDashboardCountrylist',
        ],
        'web_layout' => [
            'PageoverviewAnalysisBody',
//            'PageoverviewAnalysisTitle', // can not be cached CLI
            'PageoverviewLeadsBody',
//            'PageoverviewLeadsTitle', // can not be cached from CLI
        ],
    ];

    /**
     * @param string $route
     * @param array $arguments
     * @param OutputInterface $output
     * @return void
     * @throws ConfigurationException
     */
    public function warmup(string $route, array $arguments, OutputInterface $output): void
    {
        $this->unitFinder = GeneralUtility::makeInstance(UnitFinder::class, $arguments);
        $message = 'Cache warmup for route ' . $route;
        if ($arguments !== []) {
            $message .= ' (' . http_build_query($arguments) . ')';
        }
        $output->writeln('Start: ' . $message . ' ...');
        if (array_key_exists($route, $this->routesAndPaths) === false) {
            throw new ConfigurationException('Route ' . $route . ' not defined', 1695125113);
        }
        foreach ($this->routesAndPaths[$route] as $path) {
            $output->writeln('Path: ' . $path);
            // Call to get the HTML for caching only
            $this->unitFinder->find($path)->get();
        }
        $output->writeln('Done: ' . $message . ' successfully build up!');
    }
}
