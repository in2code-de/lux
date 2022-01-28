<?php
declare(strict_types = 1);
namespace In2code\Lux\Command;

use Doctrine\DBAL\Driver\Exception as ExceptionDbalDriver;
use In2code\Lux\Domain\Cache\CacheLayer;
use In2code\Lux\Domain\Repository\PageRepository;
use In2code\Lux\Exception\ConfigurationException;
use In2code\Lux\Exception\UnexpectedValueException;
use In2code\Lux\Utility\CacheLayerUtility;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * LuxCacheWarmupCommand
 */
class LuxCacheWarmupCommand extends Command
{
    /**
     * @var CacheLayer|null
     */
    protected $layer = null;

    /**
     * @return void
     */
    public function configure()
    {
        $this->setDescription('Warmup for caches from caching layer (e.g. dashboards).');
        $this->addArgument(
            'layers',
            InputArgument::OPTIONAL,
            'commaseparated classnames like "In2code\\Lux\\Domain\\Cache\\LeadDashboard"',
            implode(',', CacheLayerUtility::getCachelayerNames())
        );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws ConfigurationException
     * @throws ExceptionDbalDriver
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws UnexpectedValueException
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->layer = GeneralUtility::makeInstance(CacheLayer::class);
        $this->layer->flushCaches();
        $output->writeln('Flushed all lux caches');
        $configuration = CacheLayerUtility::getCachelayerConfiguration();

        foreach ($configuration as $cacheName => $classConfiguration) {
            if (GeneralUtility::inList($input->getArgument('layers'), $classConfiguration['class'])) {
                if (empty($classConfiguration['identifier'])) {
                    $this->warmupSingleLayer($output, $cacheName);
                }
                if ($classConfiguration['identifier'] === 'pageIdentifier') {
                    $this->warmupPageIdentifierLayer($output, $cacheName);
                }
            }
        }
        return 0;
    }

    /**
     * @param OutputInterface $output
     * @param string $cacheName
     * @return void
     * @throws ConfigurationException
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws UnexpectedValueException
     */
    protected function warmupSingleLayer(OutputInterface $output, string $cacheName): void
    {
        [$className, $functionName] = explode('->', $cacheName);
        $output->writeln('Warming up caches for ' . $cacheName);
        $this->layer->warmupCaches($className, $functionName);
        $output->writeln('Successfully warmed up ' . $cacheName);
    }

    /**
     * @param OutputInterface $output
     * @param string $cacheName
     * @return void
     * @throws ConfigurationException
     * @throws ExceptionDbalDriver
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws UnexpectedValueException
     */
    protected function warmupPageIdentifierLayer(OutputInterface $output, string $cacheName): void
    {
        $pageRepository = GeneralUtility::makeInstance(PageRepository::class);
        foreach ($pageRepository->getPageIdentifiersFromNormalDokTypes() as $row) {
            [$className, $functionName] = explode('->', $cacheName);
            $output->writeln('Warming up caches for ' . $cacheName . ' (page identifier ' . $row['uid'] . ')');
            $this->layer->warmupCaches($className, $functionName, (string)$row['uid']);
            $output->writeln('Successfully warmed up ' . $cacheName . ' (page identifier ' . $row['uid'] . ')');
        }
    }
}
