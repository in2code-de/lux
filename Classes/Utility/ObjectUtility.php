<?php
declare(strict_types=1);
namespace In2code\Lux\Utility;

use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Domain\Service\ConfigurationService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Class ObjectUtility
 */
class ObjectUtility
{
    /**
     * @return ObjectManager
     */
    public static function getObjectManager(): ObjectManager
    {
        /** @var ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        return $objectManager;
    }

    /**
     * @return ConfigurationService
     * @throws Exception
     */
    public static function getConfigurationService(): ConfigurationService
    {
        /** @var ConfigurationService $configurationService */
        $configurationService = self::getObjectManager()->get(ConfigurationService::class);
        return $configurationService;
    }

    /**
     * @return ContentObjectRenderer
     * @throws Exception
     */
    public static function getContentObject(): ContentObjectRenderer
    {
        return self::getObjectManager()->get(ContentObjectRenderer::class);
    }

    /**
     * @return StandaloneView
     * @throws Exception
     */
    public static function getStandaloneView(): StandaloneView
    {
        return self::getObjectManager()->get(StandaloneView::class);
    }

    /**
     * @param int $period
     * @return FilterDto
     * @throws Exception
     */
    public static function getFilterDto(int $period = FilterDto::PERIOD_DEFAULT): FilterDto
    {
        return self::getObjectManager()->get(FilterDto::class, $period);
    }

    /**
     * @param \DateTime $start
     * @param \DateTime $end
     * @return FilterDto
     * @throws Exception
     */
    public static function getFilterDtoFromStartAndEnd(\DateTime $start, \DateTime $end): FilterDto
    {
        $filterDto = self::getObjectManager()->get(FilterDto::class);
        $filterDto->setTimeFrom($start->format('c'))->setTimeTo($end->format('c'));
        return $filterDto;
    }
}
