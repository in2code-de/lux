<?php

declare(strict_types=1);
namespace In2code\Lux\Command;

use In2code\Lux\Utility\ConfigurationUtility;
use In2code\Lux\Utility\EnvironmentUtility;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

trait ExtbaseCommandTrait
{
    public function initializeExtbase(): void
    {
        if (EnvironmentUtility::isCli() && ConfigurationUtility::isTypo3Version12() === false) {
            Bootstrap::initializeBackendAuthentication();
            $configurationManager = GeneralUtility::makeInstance(ConfigurationManagerInterface::class);
            $configurationManager->setRequest(
                (new ServerRequest())->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_BE)
            );
        }
    }
}
