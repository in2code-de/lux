<?php

declare(strict_types=1);
namespace In2code\Lux\ViewHelpers\Format;

use In2code\Lux\Domain\Service\Referrer\Readable;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class ReadableReferrerViewHelper extends AbstractViewHelper
{
    public function __construct(protected readonly Readable $readable)
    {
    }

    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('domain', 'string', 'like "openai.com"', true);
    }

    public function render(): string
    {
        return $this->readable->getReadableReferrer($this->arguments['domain']);
    }
}
