<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Class Pagevisit
 */
class Pagevisit extends AbstractEntity
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
     * @var \DateTime
     */
    protected $crdate = null;

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
     * Get all pagevisits of the current visitor
     *
     * @return Pagevisit[]
     */
    public function getAllPagevisits()
    {
        return $this->getVisitor()->getPagevisits();
    }

    /**
     * @return Pagevisit|null
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
