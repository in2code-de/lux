<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Service;

use Doctrine\DBAL\Driver\Exception as ExceptionDbalDriver;
use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Domain\Model\Linklistener;
use In2code\Lux\Domain\Model\Page;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Repository\CategoryRepository;
use In2code\Lux\Domain\Repository\LinklistenerRepository;
use In2code\Lux\Domain\Repository\PageRepository;
use In2code\Lux\Events\AfterTrackingEvent;
use In2code\Lux\Utility\ConfigurationUtility;
use In2code\Lux\Utility\FrontendUtility;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;

class CategoryScoringService
{
    /**
     * @param AfterTrackingEvent $event
     * @return void
     * @throws ExceptionDbalDriver
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     * @throws ExceptionDbal
     */
    public function __invoke(AfterTrackingEvent $event): void
    {
        if ($event->getVisitor()->isNotBlacklisted()) {
            if ($event->getActionMethodName() === 'pageRequestAction') {
                $this->calculateCategoryScoringForPageRequest($event->getVisitor());
            } elseif ($event->getActionMethodName() === 'downloadRequestAction') {
                $this->calculateCategoryScoringForDownload($event->getVisitor());
            } elseif ($event->getActionMethodName() === 'linkClickRequestAction') {
                $this->calculateCategoryScoringForLinkClick($event->getVisitor());
            } elseif ($event->getActionMethodName() === 'email4LinkRequestAction') {
                $this->calculateCategoryScoringForEmail4link($event->getVisitor());
            }
        }
    }

    /**
     * @param Visitor $visitor
     * @return void
     * @throws ExceptionDbalDriver
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     * @throws ExceptionDbal
     */
    protected function calculateCategoryScoringForPageRequest(Visitor $visitor): void
    {
        $variables = $_REQUEST['tx_lux_fe'] ?? [];
        if (empty($variables['arguments']['newsUid'])) {
            $this->calculateCategoryScoringForPageRequestForPages($visitor);
        } else {
            $this->calculateCategoryScoringForPageRequestForNews($visitor);
        }
    }

    /**
     * @param Visitor $visitor
     * @return void
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    protected function calculateCategoryScoringForPageRequestForPages(Visitor $visitor): void
    {
        $pageRepository = GeneralUtility::makeInstance(PageRepository::class);
        /** @var Page $page */
        $page = $pageRepository->findByUid(FrontendUtility::getCurrentPageIdentifier());
        $categories = $page->getLuxCategories();
        foreach ($categories as $category) {
            $visitor->increaseCategoryscoringByCategory(
                ConfigurationUtility::getCategoryScoringAddPageVisit(),
                $category
            );
        }
        $pageRepository->persistAll();
    }

    /**
     * @param Visitor $visitor
     * @return void
     * @throws ExceptionDbalDriver
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws ExceptionDbal
     */
    protected function calculateCategoryScoringForPageRequestForNews(Visitor $visitor): void
    {
        $categoryRepository = GeneralUtility::makeInstance(CategoryRepository::class);
        $variables = $_REQUEST['tx_lux_fe'] ?? [];
        $categoryIdentifiers = $categoryRepository->findAllCategoryIdentifiersToNews(
            (int)$variables['arguments']['newsUid']
        );
        foreach ($categoryIdentifiers as $categoryIdentifier) {
            if ($categoryRepository->isLuxCategory($categoryIdentifier)) {
                $category = $categoryRepository->findByUid($categoryIdentifier);
                $visitor->increaseCategoryscoringByCategory(
                    ConfigurationUtility::getCategoryScoringAddNewsVisit(),
                    $category
                );
            }
        }
    }

    /**
     * @param Visitor $visitor
     * @return void
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    protected function calculateCategoryScoringForDownload(Visitor $visitor): void
    {
        $download = $visitor->getLastDownload();
        if ($download !== null) {
            if ($download->getFile() !== null && $download->getFile()->getMetadata() !== null) {
                foreach ($download->getFile()->getMetadata()->getLuxCategories() as $category) {
                    $visitor->increaseCategoryscoringByCategory(
                        ConfigurationUtility::getCategoryScoringAddDownload(),
                        $category
                    );
                }
            }
        }
    }

    /**
     * @param Visitor $visitor
     * @return void
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    protected function calculateCategoryScoringForLinkClick(Visitor $visitor): void
    {
        $llRepository = GeneralUtility::makeInstance(LinklistenerRepository::class);
        $variables = $_REQUEST['tx_lux_fe'] ?? [];
        $identifier = (int)($variables['arguments']['linklistenerIdentifier'] ?? 0);
        /** @var Linklistener $linkListener */
        $linkListener = $llRepository->findByIdentifier($identifier);
        if ($linkListener !== null) {
            $visitor->increaseCategoryscoringByCategory(
                ConfigurationUtility::getCategoryScoringLinkListenerClick(),
                $linkListener->getCategory()
            );
        }
    }

    /**
     * @param Visitor $visitor
     * @return void
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    protected function calculateCategoryScoringForEmail4link(Visitor $visitor): void
    {
        $variables = $_REQUEST['tx_lux_fe'] ?? [];
        $href = $variables['arguments']['href'] ?? '';
        $fileService = GeneralUtility::makeInstance(FileService::class);
        $file = $fileService->getFileFromHref($href);
        if ($file !== null && $file->getMetadata() !== null) {
            foreach ($file->getMetadata()->getLuxCategories() as $category) {
                $visitor->increaseCategoryscoringByCategory(
                    ConfigurationUtility::getCategoryScoringAddDownload(),
                    $category
                );
            }
        }
    }
}
