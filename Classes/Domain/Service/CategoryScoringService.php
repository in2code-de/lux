<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\Service;

use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Repository\PageRepository;
use In2code\Lux\Utility\ConfigurationUtility;
use In2code\Lux\Utility\FrontendUtility;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
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
     */
    public function calculateAndSetScoring(Visitor $visitor, string $actionMethodName)
    {
        if ($visitor->isNotBlacklisted()) {
            if ($actionMethodName === 'pageRequestAction') {
                $this->calculateCategoryScoringForPageRequest($visitor);
            } elseif ($actionMethodName === 'downloadRequestAction') {
                $this->calculateCategoryScoringForDownload($visitor);
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
     */
    protected function calculateCategoryScoringForPageRequest(Visitor $visitor)
    {
        /** @var PageRepository $pageRepository */
        $pageRepository = ObjectUtility::getObjectManager()->get(PageRepository::class);
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
     */
    protected function calculateCategoryScoringForDownload(Visitor $visitor)
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
}
