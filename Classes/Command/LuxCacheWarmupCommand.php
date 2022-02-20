<?php
declare(strict_types = 1);
namespace In2code\Lux\Command;

use In2code\Lux\Domain\Cache\CacheLayer;
use In2code\Lux\Domain\Cache\CacheWarmup;
use In2code\Lux\Domain\Repository\PageRepository;
use In2code\Lux\Exception\ConfigurationException;
use In2code\Lux\Exception\ContextException;
use In2code\Lux\Exception\UnexpectedValueException;
use In2code\Lux\Utility\CacheLayerUtility;
use In2code\Lux\Utility\ConfigurationUtility;
use In2code\Lux\Utility\ObjectUtility;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * LuxCacheWarmupCommand
 */
class LuxCacheWarmupCommand extends Command
{
    /**
     * @var OutputInterface|null
     */
    protected $output = null;

    /**
     * @var CacheWarmup|null
     */
    protected $cacheWarmup = null;

    /**
     * @return void
     */
    public function configure()
    {
        $this->setDescription('Warmup for caches from caching layer (e.g. dashboards).');
        $this->addArgument(
            'routes',
            InputArgument::OPTIONAL,
            'commaseparated routes like "lux_LuxAnalysis,lux_LuxLeads,web_layout"',
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
     * @throws ConfigurationException
     * @throws ContextException
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws RouteNotFoundException
     * @throws UnexpectedValueException
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        if (Environment::isCli() === false) {
            throw new ContextException('This command can only be exected from CLI', 1645378130);
        }

        $this->output = $output;
        $this->cacheWarmup = GeneralUtility::makeInstance(CacheWarmup::class);
        $this->flushCaches();
        foreach (GeneralUtility::trimExplode(',', $input->getArgument('routes'), true) as $route) {
            if ($route === 'web_layout' && ConfigurationUtility::isPageOverviewDisabled()) {
                continue;
            }

            $configuration = CacheLayerUtility::getCacheLayerConfigurationByRoute($route);
            if ($configuration['multiple'] === false) {
                $this->warmupSingleLayer($route, $input->getArgument('domain'), $configuration);
            } else {
                $this->warmupMultipleLayers($route, $input->getArgument('domain'), $configuration);
            }
        }
        return 0;
    }

    /**
     * @param string $route
     * @param string $domain
     * @param array $configuration
     * @return void
     * @throws RouteNotFoundException
     * @throws UnexpectedValueException
     * @throws ConfigurationException
     */
    protected function warmupSingleLayer(string $route, string $domain, array $configuration): void
    {
        $this->cacheWarmup->warmup($route, $domain, $configuration['arguments'], $this->output);
    }

    /**
     * @param string $route
     * @param string $domain
     * @param array $configuration
     * @return void
     * @throws RouteNotFoundException
     * @throws UnexpectedValueException
     * @throws ConfigurationException
     */
    protected function warmupMultipleLayers(string $route, string $domain, array $configuration): void
    {
        $pageRepository = ObjectUtility::getObjectManager()->get(PageRepository::class);
        foreach ($pageRepository->getPageIdentifiersFromNormalDokTypes() as $row) {
            $this->cacheWarmup->warmup(
                $route,
                $domain,
                $this->substituteVariablesInArguments($configuration['arguments'], $row),
                $this->output
            );
        }
    }

    /**
     * @param array $arguments
     * @param array $row
     * @return array
     */
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

    /**
     * @return void
     */
    protected function flushCaches(): void
    {
        $cacheLayer = GeneralUtility::makeInstance(CacheLayer::class);
        $cacheLayer->flushCaches();
        $this->output->writeln('Successfully flushed all lux caches');
    }
}
