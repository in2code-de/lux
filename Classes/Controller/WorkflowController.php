<?php

declare(strict_types=1);
namespace In2code\Lux\Controller;

use In2code\Lux\Domain\Model\Transfer\FilterDto;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;

/**
 * Class WorkflowController
 * Todo: Return type ": ResponseInterface" and "return $this->htmlResponse();" when TYPO3 10 support is dropped
 *       for all actions
 */
class WorkflowController extends AbstractController
{
    /**
     * @return void
     * @throws NoSuchArgumentException
     */
    public function initializeListAction(): void
    {
        $this->setFilterExtended();
    }

    /**
     * @param FilterDto $filter
     * @return void
     */
    public function listAction(FilterDto $filter): void
    {
    }
}
