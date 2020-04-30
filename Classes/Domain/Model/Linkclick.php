<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Class Linkclick
 */
class Linkclick extends AbstractEntity
{
    const TABLE_NAME = 'tx_lux_domain_model_linkclick';

    /**
     * @var \In2code\Lux\Domain\Model\Visitor
     */
    protected $visitor = null;

    /**
     * @var \DateTime|null
     */
    protected $crdate = null;

    /**
     * @var string
     */
    protected $tag = '';

    /**
     * @var \In2code\Lux\Domain\Model\Page
     */
    protected $page = null;

    /**
     * @return Visitor
     */
    public function getVisitor()
    {
        return $this->visitor;
    }

    /**
     * @param Visitor $visitor
     * @return Linkclick
     */
    public function setVisitor(Visitor $visitor): Linkclick
    {
        $this->visitor = $visitor;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCrdate(): \DateTime
    {
        return $this->crdate;
    }

    /**
     * @param \DateTime $crdate
     * @return Linkclick
     */
    public function setCrdate(\DateTime $crdate): Linkclick
    {
        $this->crdate = $crdate;
        return $this;
    }

    /**
     * @return string
     */
    public function getTag(): string
    {
        return $this->tag;
    }

    /**
     * @param string $tag
     * @return Linkclick
     */
    public function setTag(string $tag): self
    {
        $this->tag = $tag;
        return $this;
    }

    /**
     * @return Page
     */
    public function getPage(): Page
    {
        return $this->page;
    }

    /**
     * @param Page $page
     * @return Linkclick
     */
    public function setPage(Page $page): self
    {
        $this->page = $page;
        return $this;
    }
}
