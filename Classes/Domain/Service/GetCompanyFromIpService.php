<?php
declare(strict_types = 1);
namespace In2code\Lux\Domain\Service;

use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Utility\FileUtility;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;

/**
 * Class GetCompanyFromIpService
 */
class GetCompanyFromIpService
{
    /**
     * @param Visitor $visitor
     * @return string
     * @throws InvalidConfigurationTypeException
     */
    public function get(Visitor $visitor): string
    {
        $configurationService = ObjectUtility::getConfigurationService();
        $csvFile = $configurationService->getTypoScriptSettingsByPath('general.ipCompanyList');
        $string = '';
        if (FileUtility::isStringInFile($visitor->getIpAddress(), GeneralUtility::getFileAbsFileName($csvFile))) {
            $grepString = FileUtility::searchForStringInFile(
                $visitor->getIpAddress(),
                GeneralUtility::getFileAbsFileName($csvFile)
            );
            $parts = GeneralUtility::trimExplode(';', $grepString, true);
            if (!empty($parts[1])) {
                $string = $parts[1];
            }
        }
        return $string;
    }
}
