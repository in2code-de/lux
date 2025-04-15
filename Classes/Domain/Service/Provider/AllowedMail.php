<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Service\Provider;

use In2code\Lux\Utility\FileUtility;
use In2code\Lux\Utility\ObjectUtility;
use In2code\Lux\Utility\StringUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;

class AllowedMail
{
    /**
     * @param string $email
     * @return bool
     * @throws InvalidConfigurationTypeException
     */
    public function isEmailAllowed(string $email): bool
    {
        $domain = StringUtility::getDomainFromEmail($email);
        $configurationService = ObjectUtility::getConfigurationService();
        $list = $configurationService->getTypoScriptSettingsByPath('general.disallowedMailProviderList');
        return FileUtility::isExactStringInFile($domain, GeneralUtility::getFileAbsFileName($list)) === false;
    }
}
