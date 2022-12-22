<?php

declare(strict_types=1);
namespace In2code\Lux\ViewHelpers\Format;

use Closure;
use In2code\Lux\Utility\LocalizationUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class ReadableNumberViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('addTitle', 'bool', 'Surround with a span tag with a mouseover title', false, true);
    }

    /**
     * @param array $arguments
     * @param Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return string
     */
    public static function renderStatic(
        array $arguments,
        Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ): string {
        $numberOriginal = (int)$renderChildrenClosure();
        $number = self::getReadableNumber($numberOriginal);
        if ($arguments['addTitle']) {
            $thousandsSeparator = LocalizationUtility::translateByKey('number.thousandsSeparator');
            $number = '<span title="' . number_format($numberOriginal, 0, '.', $thousandsSeparator) . '">'
                . $number
                . '</span>';
        }
        return $number;
    }

    /**
     * Write "1K" for 1000 or "20M" for 20 million
     *
     * @param int $number
     * @return string
     */
    protected static function getReadableNumber(int $number): string
    {
        $decimalSeparator = LocalizationUtility::translateByKey('number.decimalSeparator');
        if ($number >= 1000000) {
            $shortened = $number / 1000000;
            $decimals = 0;
            if ($shortened < 10) {
                $decimals = 1;
            }
            $number = number_format($shortened, $decimals, $decimalSeparator, ',') . 'M';
        } elseif ($number >= 1000) {
            $shortened = $number / 1000;
            $decimals = 0;
            if ($shortened < 10) {
                $decimals = 1;
            }
            $number = number_format($shortened, $decimals, $decimalSeparator, ',') . 'K';
        }
        return (string)$number;
    }
}
