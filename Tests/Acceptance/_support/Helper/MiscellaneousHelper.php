<?php

namespace In2code\Lux\Tests\Helper;

class MiscellaneousHelper extends \Codeception\Module
{
    /**
     * This function allows to enable the fullScreenShot feature
     * for a specific test.
     *
     * The configuration is only valid vor the test where the function is called.
     * After the test the configuration is restored to the default.
     *
     *
     * @throws \Codeception\Exception\ModuleConfigException
     * @throws \Codeception\Exception\ModuleException
     */
    public function enableFullScreenShot()
    {
        $this
            ->getModule('WebDriver')
            ->moduleContainer
            ->getModule('VisualCeption')
            ->_reconfigure(['fullScreenShot' => true]);
    }

    /**
     * This function allows to disable the fullScreenShot feature
     * for a specific test.
     *
     * The configuration is only valid vor the test where the function is called.
     * After the test the configuration is restored to the default.
     *
     *
     * @throws \Codeception\Exception\ModuleConfigException
     * @throws \Codeception\Exception\ModuleException
     */
    public function disableFullScreenShot()
    {
        $this
            ->getModule('WebDriver')
            ->moduleContainer
            ->getModule('VisualCeption')
            ->_reconfigure(['fullScreenShot' => false]);
    }
}
