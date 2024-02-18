<?php

declare(strict_types=1);

namespace In2code\Lux\Domain\Service\IndividualAnalyseView\Backend\Activator;

use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Utility\DatabaseUtility;

class IsTableFilledActivator extends AbstractActivator
{
    /**
     * @return bool
     * @throws ExceptionDbal
     */
    public function isActive(): bool
    {
        $table = $this->getConfiguration()['table'] ?? '';
        return DatabaseUtility::isTableExisting($table) && DatabaseUtility::isTableFilled($table);
    }
}
