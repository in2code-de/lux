<?php
declare(strict_types = 1);
namespace In2code\Lux\Domain\Service;

use In2code\Lux\Domain\Model\Linklistener;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Repository\LinklistenerRepository;
use In2code\Lux\Domain\Repository\PageRepository;
use In2code\Lux\Utility\ConfigurationUtility;
use In2code\Lux\Utility\FrontendUtility;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;

/**
 * Class CategoryScoringService
 */
class CategoryScoringService
{
    /**
     * @param Visitor $visitor
     * @param string $actionMethodName
     * @return void
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     * @throws Exception
     */
    public function calculateAndSetScoring(Visitor $visitor, string $actionMethodName): void
    {
        if ($visitor->isNotBlacklisted()) {
            if ($actionMethodName === 'pageRequestAction') {
                $this->calculateCategoryScoringForPageRequest($visitor);
            } elseif ($actionMethodName === 'downloadRequestAction') {
                $this->calculateCategoryScoringForDownload($visitor);
            } elseif ($actionMethodName === 'linkClickRequestAction') {
                $this->calculateCategoryScoringForLinkClick($visitor);
            }
        }
    }

    /**
     * @param Visitor $visitor
     * @return void
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     * @throws Exception
     */
    protected function calculateCategoryScoringForPageRequest(Visitor $visitor): void
    {
        $pageRepository = GeneralUtility::makeInstance(PageRepository::class);
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
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws Exception
     */
    protected function calculateCategoryScoringForDownload(Visitor $visitor): void
    {
        $download = $visitor->getLastDownload();
        if ($download !== null) {
            if ($download->getFile() !== null) {
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
     * @throws Exception
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    protected function calculateCategoryScoringForLinkClick(Visitor $visitor): void
    {
        $llRepository = GeneralUtility::makeInstance(LinklistenerRepository::class);
        $identifier = (int)GeneralUtility::_GP('tx_lux_fe')['arguments']['linklistenerIdentifier'];
        /** @var Linklistener $linkListener */
        $linkListener = $llRepository->findByIdentifier($identifier);
        if ($linkListener !== null) {
            $visitor->increaseCategoryscoringByCategory(
                ConfigurationUtility::getCategoryScoringLinkListenerClick(),
                $linkListener->getCategory()
            );
        }
    }
}
