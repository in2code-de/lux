<?php

namespace In2code\Lux\Tests\Unit\Fixtures\Utility;

use In2code\Lux\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

class BackendUtilityFixture extends BackendUtility
{
    public static function getBackendUserAuthentication(): BackendUserAuthentication
    {
        return parent::getBackendUserAuthentication();
    }
}
