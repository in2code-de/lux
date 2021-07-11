<?php
declare(strict_types = 1);
namespace In2code\Lux\ViewHelpers\Charts;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class GetPercentageFromValueViewHelper
 */
class GetPercentageFromValueViewHelper extends AbstractViewHelper
{

    /**
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument('countries', 'array', 'Countries', true);
    }

    /**
     * Get percentages by visits. But don't fall under 25% to still have some visibility effects.
     * [
     *  'DE' => 200,
     *  'CH' => 70,
     *  'AT' => 6
     * ]
     *
     *  =>
     *
     * [
     *  'DE' => 100,
     *  'DE' => 35,
     *  'AT' => 25
     * ]
     * @return array
     */
    public function render(): array
    {
        $countries = $this->arguments['countries'];
        $values = array_values($countries);
        $highestValue = $values[0];
        if ($highestValue > 0) {
            foreach ($countries as $countryCode => $visits) {
                $percentuage = (int)($visits / $highestValue * 100);
                if ($percentuage < 25) {
                    $percentuage = 25;
                }
                $countries[$countryCode] = $percentuage;
            }
        }
        return $countries;
    }
}
