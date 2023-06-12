<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Service;

use DateTime;
use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Domain\Factory\CompanyFactory;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Repository\Remote\WiredmindsRepository;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Exception\ConfigurationException;
use In2code\Lux\Exception\DisabledException;
use In2code\Lux\Utility\ObjectUtility;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;

class CompanyInformationService
{
    protected VisitorRepository $visitorRepository;
    protected WiredmindsRepository $wiredmindsRepository;
    protected RequestFactory $requestFactory;
    protected CompanyFactory $companyFactory;

    protected array $settings = [];

    public function __construct(
        VisitorRepository $visitorRepository,
        WiredmindsRepository $wiredmindsRepository,
        RequestFactory $requestFactory,
        CompanyFactory $companyFactory
    ) {
        $this->visitorRepository = $visitorRepository;
        $this->wiredmindsRepository = $wiredmindsRepository;
        $this->requestFactory = $requestFactory;
        $this->companyFactory = $companyFactory;
        $configurationService = ObjectUtility::getConfigurationService();
        $this->settings = $configurationService->getTypoScriptSettings();
    }

    /**
     * @param int $limit
     * @param bool $overwriteExisting
     * @param DateTime $time
     * @param OutputInterface $output
     * @return int return hits
     * @throws ConfigurationException
     * @throws DisabledException
     * @throws ExceptionDbal
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function setCompaniesToExistingVisitors(
        int $limit,
        bool $overwriteExisting,
        DateTime $time,
        OutputInterface $output
    ): int {
        if ($this->isEnabled() === false) {
            throw new DisabledException('Wiredminds connection is not enabled in TypoScript setup', 1686585329);
        }

        $records = $this->visitorRepository->findLatestVisitorsWithIpAddress($limit, $time, !$overwriteExisting);
        $counter = 0;
        $progress = new ProgressBar($output, count($records));
        $progress->start();
        foreach ($records as $visitorIdentifier => $ipAddress) {
            /** @var Visitor $visitor */
            $visitor = $this->visitorRepository->findByUid($visitorIdentifier);
            if ($visitor !== null) {
                $hit = $visitor->setCompanyrecordByIpAdressFromInterface($ipAddress);
                if ($hit === true) {
                    $counter++;
                }
            }
            $progress->advance();
        }
        return $counter;
    }

    protected function isEnabled(): bool
    {
        return ($this->settings['tracking']['company']['_enable'] ?? '0') === '1';
    }
}
