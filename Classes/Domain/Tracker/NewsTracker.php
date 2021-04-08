<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\Tracker;

use In2code\Lux\Domain\Model\News;
use In2code\Lux\Domain\Model\Newsvisit;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Repository\NewsRepository;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Signal\SignalTrait;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException;

/**
 * Class PageTracker
 */
class NewsTracker
{
    use SignalTrait;

    /**
     * @var VisitorRepository|null
     */
    protected $visitorRepository = null;

    /**
     * PageTracker constructor.
     */
    public function __construct()
    {
        $this->visitorRepository = ObjectUtility::getObjectManager()->get(VisitorRepository::class);
    }

    /**
     * @param Visitor $visitor
     * @param array $arguments
     * @return void
     * @throws Exception
     * @throws IllegalObjectTypeException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws UnknownObjectException
     */
    public function track(Visitor $visitor, array $arguments): void
    {
        if ($this->isTrackingActivated($visitor, $arguments)) {
            $visitor->addNewsvisit($this->getNewsvisit((int)$arguments['newsUid'], (int)$arguments['languageUid']));
            $this->visitorRepository->update($visitor);
            $this->visitorRepository->persistAll();
            $this->signalDispatch(__CLASS__, __METHOD__, [$visitor]);
        }
    }

    /**
     * @param int $newsUid
     * @param int $languageUid
     * @return Newsvisit
     * @throws Exception
     */
    protected function getNewsvisit(int $newsUid, int $languageUid): Newsvisit
    {
        /** @var Newsvisit $newsvisit */
        $newsvisit = ObjectUtility::getObjectManager()->get(Newsvisit::class);
        $newsRepository = ObjectUtility::getObjectManager()->get(NewsRepository::class);
        /** @var News $news */
        $news = $newsRepository->findByUid($newsUid);
        $newsvisit->setNews($news)->setLanguage($languageUid)->setDomain();
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
