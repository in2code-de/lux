<?php

namespace In2code\Lux\Tests\Acceptance\Backend;

use In2code\Lux\Tests\AcceptanceTester;

class AnalyseDashboardCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->loginToBackend($I);
    }

    public function loginToBackendSuccessfully(AcceptanceTester $I)
    {
        $I->waitForElement('.modulemenu', 30);

        $I->scrollTo('//span[contains(@class, "modulemenu-name") and text()="LUX"]');

        $luxButton = '//button[@data-modulemenu-identifier="lux_module"]';
        $isExpanded = $I->grabAttributeFrom($luxButton, 'aria-expanded');

        if ($isExpanded !== 'true') {
            $I->click($luxButton);
        }

        $I->wait(1);

        $I->scrollTo('//span[contains(@class, "modulemenu-name") and text()="Analysis"]');
        $I->click('//span[contains(@class, "modulemenu-name") and text()="Analysis"]');

        $I->wait(2);

        $I->switchToIFrame('list_frame');
        $I->waitForElementVisible('body', 10); // Waits for the body element to be visible in the IFrame

        $dashboardElements = [
            '//h3[contains(@class, "panel-title") and contains(text(), "Pagevisits")]',
            '//h3[contains(@class, "panel-title") and contains(text(), "Downloads")]',
            '//h3[contains(@class, "panel-title") and contains(text(), "Activity log")]'
        ];

        foreach ($dashboardElements as $element) {
            $I->seeElement($element);
        }

        $topSections = [
            '//h3[contains(@class, "panel-title") and contains(text(), "Top 10 most visited pages")]',
            '//h3[contains(@class, "panel-title") and contains(text(), "Top 10 downloads")]',
            '//h3[contains(@class, "panel-title") and contains(text(), "Top 10 most visited news")]',
            '//h3[contains(@class, "panel-title") and contains(text(), "Top 10 most used searchterms")]',
            '//h3[contains(@class, "panel-title") and contains(text(), "Top Social Media Sources")]'
        ];

        foreach ($topSections as $section) {
            $I->seeElement($section);
        }

        $expectedContent = [
            '//span[@data-page-identifier="UID1" and contains(text(), "Start")]',
            '//span[@data-download-uid="3" and contains(text(), "productb.pdf")]',
            '//span[@data-news-identifier="2" and contains(text(), "Testnews 2")]',
            '//span[contains(text(), "LUX TYPO3")]'
        ];

        foreach ($expectedContent as $content) {
            $I->seeElement($content);
        }

        $I->switchToIFrame(); // Back to main context

        // Further tests...
        //$I->see('Seitenaufrufe');
        //$I->selectOption('#time', ['value' => '1']);
    }
}
