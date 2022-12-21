<?php

declare(strict_types=1);
namespace In2code\Lux\Controller;

use In2code\Lux\Domain\Model\Transfer\FilterDto;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;

class WorkflowController extends AbstractController
{
    /**
     * @return void
     * @throws NoSuchArgumentException
     */
    public function initializeListAction(): void
    {
        $this->setFilter();
    }

    /**
     * @param FilterDto $filter
     * @return ResponseInterface
     */
    public function listAction(FilterDto $filter): ResponseInterface
    {
        return $this->htmlResponse();
    }
}
