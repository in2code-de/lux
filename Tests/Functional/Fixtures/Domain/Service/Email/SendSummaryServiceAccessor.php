<?php

declare(strict_types=1);
namespace In2code\Lux\Tests\Functional\Fixtures\Domain\Service\Email;

use In2code\Lux\Domain\Service\Email\SendSummaryService;

class SendSummaryServiceAccessor extends SendSummaryService
{
    public function renderMailTemplate(): string
    {
        return $this->getMailTemplate();
    }
}
