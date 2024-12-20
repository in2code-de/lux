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
        $I->click('Analyse');

        // Warte auf den Container, in dem die AJAX-Inhalte geladen werden
        $I->waitForElement('.panel-heading', 30);

        // Alternative Methoden zum Warten:
        $I->waitForText('Top 10', 30);  // Wartet bis zu 30 Sekunden auf den Text
        // ODER
        $I->waitForAjax(30);  // Wartet auf AJAX-Requests

        // Jetzt erst nach dem Text suchen
        $I->see('Top 10');

        // Optional: Debug-Hilfen
        $I->makeScreenshot('after_ajax_load');

        // Weitere Tests...
        //$I->see('Seitenaufrufe');
        //$I->selectOption('#time', ['value' => '1']);
    }
}
