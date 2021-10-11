<?php
declare(strict_types = 1);
namespace In2code\Lux\Domain\Tracker;

use In2code\Lux\Domain\Model\News;
use In2code\Lux\Domain\Model\Newsvisit;
use In2code\Lux\Domain\Model\Pagevisit;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Repository\NewsRepository;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Signal\SignalTrait;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;

/**
 * Class PageTracker
 */
class NewsTracker
{
    use SignalTrait;

    /**
     * @var VisitorRepository
     */
    protected $visitorRepository;

    /**
     * Constructor
     *
     * @param VisitorRepository $visitorRepository
     */
    public function __construct(VisitorRepository $visitorRepository)
    {
        $this->visitorRepository = $visitorRepository;
    }

    /**
     * @param Visitor $visitor
     * @param array $arguments
     * @param Pagevisit|null $pagevisit
     * @return void
     * @throws Exception
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function track(Visitor $visitor, array $arguments, Pagevisit $pagevisit = null): void
    {
        if ($this->isTrackingActivated($visitor, $arguments)) {
            $visitor->addNewsvisit($this->getNewsvisit(
                (int)$arguments['newsUid'],
                (int)$arguments['languageUid'],
                $pagevisit
            ));
            $this->visitorRepository->update($visitor);
            $this->visitorRepository->persistAll();
            $this->signalDispatch(__CLASS__, __METHOD__, [$visitor]);
        }
    }

    /**
     * @param int $newsUid
     * @param int $languageUid
     * @param Pagevisit|null $pagevisit
     * @return Newsvisit
     * @throws Exception
     */
    protected function getNewsvisit(int $newsUid, int $languageUid, Pagevisit $pagevisit = null): Newsvisit
    {
        /** @var Newsvisit $newsvisit */
        $newsvisit = ObjectUtility::getObjectManager()->get(Newsvisit::class);
        $newsRepository = ObjectUtility::getObjectManager()->get(NewsRepository::class);
        /** @var News $news */
        $news = $newsRepository->findByUid($newsUid);
        $newsvisit->setNews($news)->setLanguage($languageUid)->setDomain()->setPagevisit($pagevisit);
        return $newsvisit;
    }

    /**
     * @param Visitor $visitor
     * @param array $arguments
     * @return bool
     * @throws Exception
     */
    protected function isTrackingActivated(Visitor $visitor, array $arguments): bool
    {
        return !empty($arguments['newsUid']) && $visitor->isNotBlacklisted() && $this->isTrackingActivatedInSettings();
    }

    /**
     * Check if tracking of pagevisits is turned on via TypoScript
     *
     * @return bool
     * @throws Exception
     */
    protected function isTrackingActivatedInSettings(): bool
    {
        $configurationService = ObjectUtility::getConfigurationService();
        $settings = $configurationService->getTypoScriptSettings();
        return !empty($settings['tracking']['pagevisits']['_enable'])
            && $settings['tracking']['pagevisits']['_enable'] === '1';
    }
}
