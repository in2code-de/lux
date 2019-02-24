<?php
declare(strict_types=1);
namespace In2code\Lux\ViewHelpers\Format;

use In2code\Lux\Utility\LocalizationUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class ReadableDateViewHelper
 */
class ReadableDateViewHelper extends AbstractViewHelper
{

    /**
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('date', \DateTime::class, 'Datetime', false);
    }

    /**
     * @return string
     */
    public function render(): string
    {
        $date = $this->getDate();
        $deltaTimestamp = time() - $date->getTimestamp();
        $delta = $date->diff(new \DateTime());

        if ($deltaTimestamp < 3600) {
            return $this->renderMinutes($delta);
        } elseif ($deltaTimestamp < 86400) {
            return $this->renderHours($delta);
        } elseif ($deltaTimestamp < 604800) {
            return $this->renderDays($delta);
        } else {
            return $this->renderDate($date);
        }
    }

    /**
     * @param \DateInterval $date
     * @return string
     */
    protected function renderMinutes(\DateInterval $date): string
    {
        $minutes = $date->i;
        return (string)LocalizationUtility::translate(
            'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:readabledate.minutes',
            'Lux',
            [$minutes]
        );
    }

    /**
     * @param \DateInterval $date
     * @return string
     */
    protected function renderHours(\DateInterval $date): string
    {
        $hours = $date->h;
        return (string)LocalizationUtility::translate(
            'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:readabledate.hours',
            'Lux',
            [$hours]
        );
    }

    /**
     * @param \DateInterval $date
     * @return string
     */
    protected function renderDays(\DateInterval $date): string
    {
        $days = $date->d;
        return (string)LocalizationUtility::translate(
            'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:readabledate.days',
            'Lux',
            [$days]
        );
    }

    /**
     * @param \DateTime $date
     * @return string
     */
    protected function renderDate(\DateTime $date): string
    {
        $format = (string)LocalizationUtility::translate(
            'LLL:EXT:lux/Resources/Private/Language/locallang_db.xlf:readabledate.date'
        );
        return $date->format($format);
    }

    /**
     * @return \DateTime
     */
    protected function getDate(): \DateTime
    {
        $pathAndFilename = $this->renderChildren();
        if (!empty($this->arguments['date'])) {
            $pathAndFilename = $this->arguments['date'];
        }
        return $pathAndFilename;
    }
}
