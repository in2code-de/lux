<?php

declare(strict_types=1);
namespace In2code\Lux\Utility;

use DateTime;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Domain\Service\ConfigurationService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class ObjectUtility
{
    public static function getConfigurationService(): ConfigurationService
    {
        /** @var ConfigurationService $configurationService */
        $configurationService = GeneralUtility::makeInstance(ConfigurationService::class);
        return $configurationService;
    }

    public static function getContentObject(): ContentObjectRenderer
    {
        return GeneralUtility::makeInstance(ContentObjectRenderer::class);
    }

    public static function getStandaloneView(): StandaloneView
    {
        return GeneralUtility::makeInstance(StandaloneView::class);
    }

    public static function getFilterDto(int $period = FilterDto::PERIOD_DEFAULT): FilterDto
    {
        return GeneralUtility::makeInstance(FilterDto::class, $period);
    }

    public static function getFilterDtoFromStartAndEnd(DateTime $start, DateTime $end): FilterDto
    {
        $filterDto = GeneralUtility::makeInstance(FilterDto::class);
        $filterDto->setTimeFrom($start->format('c'))->setTimeTo($end->format('c'));
        return $filterDto;
    }
}
