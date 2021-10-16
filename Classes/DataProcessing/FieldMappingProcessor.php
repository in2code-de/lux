<?php
namespace In2code\Lux\DataProcessing;

use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

/**
 * Class FieldMappingProcessor
 */
class FieldMappingProcessor implements DataProcessorInterface
{
    /**
     * @param ContentObjectRenderer $cObj The data of the content element or page
     * @param array $contentObjectConfiguration The configuration of Content Object
     * @param array $processorConfiguration The configuration of this processor
     * @param array $processedData Key/value store of processed data (e.g. to be passed to a Fluid View)
     * @return array the processed data as key/value store
     * @throws Exception
     */
    public function process(
        ContentObjectRenderer $cObj,
        array $contentObjectConfiguration,
        array $processorConfiguration,
        array $processedData
    ) {
        $fieldMapping = $this->getFieldMappingSettings($contentObjectConfiguration);
        $processedData['fieldMapping'] = json_encode($fieldMapping);
        return $processedData;
    }

    /**
     * @param array $contentObjectConfiguration
     * @return mixed
     */
    protected function getFieldMappingSettings(array $contentObjectConfiguration): array
    {
        $fieldMapping = [];
        $typoscriptService = GeneralUtility::makeInstance(TypoScriptService::class);
        $settings = $typoscriptService->convertTypoScriptArrayToPlainArray(
            (array)$contentObjectConfiguration['settings.']
        );
        if (!empty($settings['identification']['fieldMapping'])) {
            $fieldMapping = $settings['identification']['fieldMapping'];
        }
        return $fieldMapping;
    }
}
