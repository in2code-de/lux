<?php

namespace In2code\Lux\Tests\Unit\Fixtures\Utility;

use In2code\Lux\Utility\CacheLayerUtility;

/**
 * Class CacheLayerUtilityFixture
 */
class CacheLayerUtilityFixture extends CacheLayerUtility
{
    /**
     * @return array
     */
    public static function getCachelayerConfiguration(): array
    {
        return parent::getCachelayerConfiguration();
    }
}
