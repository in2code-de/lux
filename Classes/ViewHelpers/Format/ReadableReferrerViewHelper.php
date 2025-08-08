<?php

declare(strict_types=1);
namespace In2code\Lux\ViewHelpers\Format;

use In2code\Lux\Domain\Service\Referrer\SourceHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class ReadableReferrerViewHelper extends AbstractViewHelper
{
    public function __construct(protected readonly SourceHelper $sourceHelper)
    {
    }

    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('domain', 'string', 'like "openai.com"', true);
    }

    public function render(): string
    {
        return $this->sourceHelper->getReadableReferrer($this->arguments['domain']);
    }
}
