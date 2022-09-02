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
     * @throws \Exception
     */
    public function render(): string
    {
        $date = $this->getDate();
        $deltaTimestamp = time() - $date->getTimestamp();
        $delta = $date->diff(new \DateTime());

        if ($deltaTimestamp < 3600) {
            return $this->renderMinutes($delta);
        }
        if ($deltaTimestamp < 86400) {
            return $this->renderHours($delta);
        }
        if ($deltaTimestamp < 604800) {
            return $this->renderDays($delta);
        }
        return $this->renderDate($date);
    }

    /**
     * @param \DateInterval $date
     * @return string
     */
    protected function renderMinutes(\DateInterval $date): string
    {
        $minutes = $date->i;
        return (string)LocalizationUtility::translateByKey('readabledate.minutes', [$minutes]);
    }

    /**
     * @param \DateInterval $date
     * @return string
     */
    protected function renderHours(\DateInterval $date): string
    {
        $hours = $date->h;
        return (string)LocalizationUtility::translateByKey('readabledate.hours', [$hours]);
    }

    /**
     * @param \DateInterval $date
     * @return string
     */
    protected function renderDays(\DateInterval $date): string
    {
        $days = $date->d;
        return (string)LocalizationUtility::translateByKey('readabledate.days', [$days]);
    }

    /**
     * @param \DateTime $date
     * @return string
     */
    protected function renderDate(\DateTime $date): string
    {
        $format = (string)LocalizationUtility::translateByKey('readabledate.date');
        return $date->format($format);
    }

    /**
     * @return \DateTime
     * @throws \Exception
     */
    protected function getDate(): \DateTime
    {
        $date = $this->renderChildren();
        if (!empty($this->arguments['date'])) {
            $date = $this->arguments['date'];
        }
        if ($date === null) {
            $date = new \DateTime();
        }
        return $date;
    }
}
