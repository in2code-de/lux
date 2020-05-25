<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\Model;

use In2code\Lux\Domain\Service\ReadableReferrerService;
use In2code\Lux\Utility\FrontendUtility;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Extbase\Object\Exception;

/**
 * Class Pagevisit
 */
class Pagevisit extends AbstractModel
{
    const TABLE_NAME = 'tx_lux_domain_model_pagevisit';

    /**
     * @var \In2code\Lux\Domain\Model\Visitor
     */
    protected $visitor = null;

    /**
     * @var \In2code\Lux\Domain\Model\Page
     */
    protected $page = null;

    /**
     * @var int
     */
    protected $language = 0;

    /**
     * @var \DateTime
     */
    protected $crdate = null;

    /**
     * @var string
     */
    protected $referrer = '';

    /**
     * @var string
     */
    protected $domain = '';

    /**
     * @return Visitor
     */
    public function getVisitor()
    {
        return $this->visitor;
    }

    /**
     * @param Visitor $visitor
     * @return Pagevisit
     */
    public function setVisitor(Visitor $visitor)
    {
        $this->visitor = $visitor;
        return $this;
    }

    /**
     * @return Page
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param Page $page
     * @return Pagevisit
     */
    public function setPage(Page $page)
    {
        $this->page = $page;
        return $this;
    }

    /**
     * @return int
     */
    public function getLanguage(): int
    {
        return $this->language;
    }

    /**
     * @param int $language
     * @return Pagevisit
     */
    public function setLanguage(int $language): self
    {
        $this->language = $language;
        return $this;
    }

    /**
     * @return \DateTime
     * @throws \Exception
     */
    public function getCrdate(): \DateTime
    {
        $crdate = $this->crdate;
        if ($crdate === null) {
            $crdate = new \DateTime();
        }
        return $crdate;
    }

    /**
     * @param \DateTime $crdate
     * @return Pagevisit
     */
    public function setCrdate(\DateTime $crdate)
    {
        $this->crdate = $crdate;
        return $this;
    }

    /**
     * @return string
     */
    public function getReferrer(): string
    {
        return $this->referrer;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getReadableReferrer(): string
    {
        $referrerService = ObjectUtility::getObjectManager()->get(ReadableReferrerService::class, $this->getReferrer());
        return $referrerService->getReadableReferrer();
    }

    /**
     * @param string $referrer
     * @return Pagevisit
     */
    public function setReferrer(string $referrer): self
    {
        $this->referrer = $referrer;
        return $this;
    }

    /**
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * @return Pagevisit
     */
    public function setDomain(): self
    {
        $this->domain = FrontendUtility::getCurrentDomain();
        return $this;
    }

    /**
     * Get all pagevisits of the current visitor
     *
     * @return Pagevisit[]
     * @throws \Exception
     */
    public function getAllPagevisits()
    {
        return $this->getVisitor()->getPagevisits();
    }

    /**
     * @return Pagevisit|null
     * @throws \Exception
     */
    public function getNextPagevisit()
    {
        $allPagevisits = $this->getAllPagevisits();
        $thisFound = false;
        $nextPagevisit = null;
        foreach ($allPagevisits as $pagevisit) {
            if ($thisFound === true) {
                $nextPagevisit = $pagevisit;
                break;
            }
            if ($pagevisit === $this) {
                $thisFound = true;
            }
        }
        return $nextPagevisit;
    }

    /**
     * @return Pagevisit|null
     * @throws \Exception
     */
    public function getPreviousPagevisit()
    {
        $allPagevisits = $this->getAllPagevisits();
        $previousPagevisit = $lastPagevisit = null;
        foreach ($allPagevisits as $pagevisit) {
            if ($pagevisit === $this) {
                $previousPagevisit = $lastPagevisit;
            }
            $lastPagevisit = $pagevisit;
        }
        return $previousPagevisit;
    }
}
