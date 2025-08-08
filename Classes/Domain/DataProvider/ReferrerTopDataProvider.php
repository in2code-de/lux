<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\DataProvider;

use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Domain\Repository\PagevisitRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ReferrerTopDataProvider extends AbstractDataProvider
{
    /**
     * Set values like:
     *  [
     *      'amounts' => [
     *          120,
     *          88
     *      ],
     *      'titles' => [
     *          'www.foo.com',
     *          'www.bar.com',
     *      ]
     *  ]
     *
     * @return void
     * @throws ExceptionDbal
     */
    public function prepareData(): void
    {
        /** @var PagevisitRepository $pagevisitRepository */
        $pagevisitRepository = GeneralUtility::makeInstance(PagevisitRepository::class);
        $titles = $amounts = [];
        foreach ($pagevisitRepository->getAmountOfReferrerDomains($this->filter->setLimit(5)) as $source) {
            $titles[] = $source['referrer_domain'];
            $amounts[] = $source['count'];
        }
        $this->data = ['amounts' => $amounts, 'titles' => $titles];
    }
}
