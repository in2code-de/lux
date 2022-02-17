<?php
declare(strict_types = 1);
namespace In2code\Lux\Command;

use In2code\Lux\Domain\Cache\CacheLayer;
use In2code\Lux\Domain\Cache\CacheWarmup;
use In2code\Lux\Exception\UnexpectedValueException;
use In2code\Lux\Utility\CacheLayerUtility;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * LuxCacheWarmupCommand
 */
class LuxCacheWarmupCommand extends Command
{
    /**
     * @return void
     */
    public function configure()
    {
        $this->setDescription('Warmup for caches from caching layer (e.g. dashboards).');
        $this->addArgument(
            'routes',
            InputArgument::OPTIONAL,
            'commaseparated routes like "lux_LuxAnalysis,lux_LuxLeads"',
            implode(',', CacheLayerUtility::getCachelayerRoutes())
        );
        $this->addArgument(
            'domain',
            InputArgument::OPTIONAL,
            'Specify domain if no domain in siteconfiguration like "https://domain.org"',
            ''
        );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws UnexpectedValueException
     * @throws RouteNotFoundException
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $cacheWarmup = GeneralUtility::makeInstance(CacheWarmup::class);
        $this->flushCaches($output);
        foreach (GeneralUtility::trimExplode(',', $input->getArgument('routes'), true) as $route) {
            $cacheWarmup->warmup($route, $input->getArgument('domain'), $output);
        }
        return 0;
    }

    /**
     * @param OutputInterface $output
     * @return void
     */
    protected function flushCaches(OutputInterface $output): void
    {
        $cacheLayer = GeneralUtility::makeInstance(CacheLayer::class);
        $cacheLayer->flushCaches();
        $output->writeln('Successfully flushed all lux caches');
    }
}
