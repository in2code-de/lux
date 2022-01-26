<?php

declare(strict_types = 1);

namespace In2code\Lux\Domain\Cache;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Domain\Repository\CategoryRepository;
use In2code\Lux\Domain\Repository\DownloadRepository;
use In2code\Lux\Domain\Repository\FingerprintRepository;
use In2code\Lux\Domain\Repository\IpinformationRepository;
use In2code\Lux\Domain\Repository\LinkclickRepository;
use In2code\Lux\Domain\Repository\LinklistenerRepository;
use In2code\Lux\Domain\Repository\LogRepository;
use In2code\Lux\Domain\Repository\NewsRepository;
use In2code\Lux\Domain\Repository\NewsvisitRepository;
use In2code\Lux\Domain\Repository\PageRepository;
use In2code\Lux\Domain\Repository\PagevisitRepository;
use In2code\Lux\Domain\Repository\SearchRepository;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Exception\ConfigurationException;
use In2code\Lux\Exception\UnexpectedValueException;
use In2code\Lux\Utility\CacheLayerUtility;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Extbase\Object\Exception as ExceptionExtbaseObject;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;

/**
 * AbstractLayer
 */
abstract class AbstractLayer
{
    /**
     * @var string
     */
    protected $cacheName = '';

    /**
     * @var string
     */
    protected $identifier = '';

    /**
     * @var VisitorRepository
     */
    protected $visitorRepository = null;

    /**
     * @var IpinformationRepository
     */
    protected $ipinformationRepository = null;

    /**
     * @var LogRepository
     */
    protected $logRepository = null;

    /**
     * @var PagevisitRepository
     */
    protected $pagevisitsRepository = null;

    /**
     * @var PageRepository
     */
    protected $pageRepository = null;

    /**
     * @var DownloadRepository
     */
    protected $downloadRepository = null;

    /**
     * @var NewsvisitRepository
     */
    protected $newsvisitRepository = null;

    /**
     * @var NewsRepository
     */
    protected $newsRepository = null;

    /**
     * @var CategoryRepository
     */
    protected $categoryRepository = null;

    /**
     * @var LinkclickRepository
     */
    protected $linkclickRepository = null;

    /**
     * @var LinklistenerRepository
     */
    protected $linklistenerRepository = null;

    /**
     * @var FingerprintRepository
     */
    protected $fingerprintRepository = null;

    /**
     * @var SearchRepository
     */
    protected $searchRepository = null;

    /**
     * @param VisitorRepository $visitorRepository
     * @param IpinformationRepository $ipinformationRepository
     * @param LogRepository $logRepository
     * @param PagevisitRepository $pagevisitsRepository
     * @param PageRepository $pageRepository
     * @param DownloadRepository $downloadRepository
     * @param NewsvisitRepository $newsvisitRepository
     * @param NewsRepository $newsRepository
     * @param CategoryRepository $categoryRepository
     * @param LinkclickRepository $linkclickRepository
     * @param LinklistenerRepository $linklistenerRepository
     * @param FingerprintRepository $fingerprintRepository
     * @param SearchRepository $searchRepository
     */
    public function __construct(
        VisitorRepository $visitorRepository = null,
        IpinformationRepository $ipinformationRepository = null,
        LogRepository $logRepository = null,
        PagevisitRepository $pagevisitsRepository = null,
        PageRepository $pageRepository = null,
        DownloadRepository $downloadRepository = null,
        NewsvisitRepository $newsvisitRepository = null,
        NewsRepository $newsRepository = null,
        CategoryRepository $categoryRepository = null,
        LinkclickRepository $linkclickRepository = null,
        LinklistenerRepository $linklistenerRepository = null,
        FingerprintRepository $fingerprintRepository = null,
        SearchRepository $searchRepository = null
    ) {
        if ($visitorRepository === null) {
            $visitorRepository = ObjectUtility::getObjectManager()->get(VisitorRepository::class);
            $ipinformationRepository = ObjectUtility::getObjectManager()->get(IpinformationRepository::class);
            $logRepository = ObjectUtility::getObjectManager()->get(LogRepository::class);
            $pagevisitsRepository = ObjectUtility::getObjectManager()->get(PagevisitRepository::class);
            $pageRepository = ObjectUtility::getObjectManager()->get(PageRepository::class);
            $downloadRepository = ObjectUtility::getObjectManager()->get(DownloadRepository::class);
            $newsvisitRepository = ObjectUtility::getObjectManager()->get(NewsvisitRepository::class);
            $newsRepository = ObjectUtility::getObjectManager()->get(NewsRepository::class);
            $categoryRepository = ObjectUtility::getObjectManager()->get(CategoryRepository::class);
            $linkclickRepository = ObjectUtility::getObjectManager()->get(LinkclickRepository::class);
            $linklistenerRepository = ObjectUtility::getObjectManager()->get(LinklistenerRepository::class);
            $fingerprintRepository = ObjectUtility::getObjectManager()->get(FingerprintRepository::class);
            $searchRepository = ObjectUtility::getObjectManager()->get(SearchRepository::class);
        }
        $this->visitorRepository = $visitorRepository;
        $this->ipinformationRepository = $ipinformationRepository;
        $this->logRepository = $logRepository;
        $this->pagevisitsRepository = $pagevisitsRepository;
        $this->pageRepository = $pageRepository;
        $this->downloadRepository = $downloadRepository;
        $this->newsvisitRepository = $newsvisitRepository;
        $this->newsRepository = $newsRepository;
        $this->categoryRepository = $categoryRepository;
        $this->linkclickRepository = $linkclickRepository;
        $this->linklistenerRepository = $linklistenerRepository;
        $this->fingerprintRepository = $fingerprintRepository;
        $this->searchRepository = $searchRepository;
    }

    /**
     * @return array
     * @throws DBALException
     * @throws ExceptionDbal
     * @throws InvalidConfigurationTypeException
     * @throws ExceptionExtbaseObject
     * @throws InvalidQueryException
     */
    public function getAllArguments(): array
    {
        return array_merge($this->getCachableArguments(), $this->getUncachableArguments());
    }

    /**
     * @param string $cacheName
     * @param string $identifier
     * @return void
     */
    public function initialize(string $cacheName, string $identifier): void
    {
        $this->cacheName = $cacheName;
        $this->identifier = $identifier;
    }

    /**
     * @return int
     * @throws ConfigurationException
     * @throws UnexpectedValueException
     */
    public function getCacheLifetime(): int
    {
        if ($this->cacheName === '') {
            throw new ConfigurationException('CacheName must not be empty', 1636364317);
        }
        return CacheLayerUtility::getCachelayerLifetimeByCacheName($this->cacheName);
    }
}
