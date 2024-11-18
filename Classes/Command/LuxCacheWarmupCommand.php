<?php

declare(strict_types=1);
namespace In2code\Lux\Command;

use Doctrine\DBAL\Driver\Exception as ExceptionDbalDriver;
use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Domain\Cache\CacheLayer;
use In2code\Lux\Domain\Cache\CacheWarmup;
use In2code\Lux\Domain\Repository\PageRepository;
use In2code\Lux\Exception\ConfigurationException;
use In2code\Lux\Exception\ContextException;
use In2code\Lux\Utility\CacheLayerUtility;
use In2code\Lux\Utility\ConfigurationUtility;
use In2code\Lux\Utility\EnvironmentUtility;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

class LuxCacheWarmupCommand extends Command
{
    use ExtbaseCommandTrait;

    protected ?OutputInterface $output = null;
    protected ?CacheWarmup $cacheWarmup = null;

    public function configure()
    {
        $this->setDescription('Warmup for caches from caching layer (e.g. dashboards).');
        $this->addArgument(
            'routes',
            InputArgument::OPTIONAL,
            'commaseparated routes like "lux_LuxAnalysis,lux_LuxLead,web_layout"',
            implode(',', CacheLayerUtility::getCachelayerRoutes())
        );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws ConfigurationException
     * @throws ContextException
     * @throws ExceptionDbalDriver
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws ExceptionDbal
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        if (EnvironmentUtility::isCli() === false) {
            throw new ContextException('This command can only be executed from CLI', 1645378130);
        }
        $this->initializeExtbase();

        $this->output = $output;
        $this->cacheWarmup = GeneralUtility::makeInstance(CacheWarmup::class);
        $this->flushCaches();
        foreach (GeneralUtility::trimExplode(',', $input->getArgument('routes'), true) as $route) {
            if ($route === 'web_layout' && ConfigurationUtility::isPageOverviewDisabled()) {
                continue;
            }

            $configuration = CacheLayerUtility::getCacheLayerConfigurationByRoute($route);
            if ($configuration['multiple'] === false) {
                $this->warmupSingleLayer($route, $configuration);
            } else {
                $this->warmupMultipleLayers($route, $configuration);
            }
        }
        return self::SUCCESS;
    }

    /**
     * @param string $route
     * @param array $configuration
     * @return void
     * @throws ConfigurationException
     */
    protected function warmupSingleLayer(string $route, array $configuration): void
    {
        $this->cacheWarmup->warmup($route, $configuration['arguments'], $this->output);
    }

    /**
     * @param string $route
     * @param array $configuration
     * @return void
     * @throws ExceptionDbalDriver
     * @throws ConfigurationException
     * @throws ExceptionDbal
     */
    protected function warmupMultipleLayers(string $route, array $configuration): void
    {
        $pageRepository = GeneralUtility::makeInstance(PageRepository::class);
        foreach ($pageRepository->getPageIdentifiersFromNormalDokTypes() as $row) {
            $this->cacheWarmup->warmup(
                $route,
                $this->substituteVariablesInArguments($configuration['arguments'], $row),
                $this->output
            );
        }
    }

    protected function substituteVariablesInArguments(array $arguments, array $row): array
    {
        foreach ($arguments as $key => $value) {
            if (stristr($value, '{')) {
                $standaloneView = GeneralUtility::makeInstance(StandaloneView::class);
                $standaloneView->setTemplateSource($value);
                $standaloneView->assignMultiple($row);
                $arguments[$key] = $standaloneView->render();
            }
        }
        return $arguments;
    }

    protected function flushCaches(): void
    {
        $cacheLayer = GeneralUtility::makeInstance(CacheLayer::class);
        $cacheLayer->flushCaches();
        $this->output->writeln('Successfully flushed all lux caches');
    }
}
