<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\Model;

/**
 * Class Linkclick
 */
class Linkclick extends AbstractModel
{
    const TABLE_NAME = 'tx_lux_domain_model_linkclick';

    /**
     * @var \DateTime|null
     */
    protected $crdate = null;

    /**
     * @var \In2code\Lux\Domain\Model\Linklistener
     */
    protected $linklistener = null;

    /**
     * @var \In2code\Lux\Domain\Model\Page
     */
    protected $page = null;

    /**
     * @var \In2code\Lux\Domain\Model\Visitor
     */
    protected $visitor = null;

    /**
     * @return \DateTime|null
     */
    public function getCrdate(): ?\DateTime
    {
        return $this->crdate;
    }

    /**
     * @param \DateTime|null $crdate
     * @return Linkclick
     */
    public function setCrdate(\DateTime $crdate): self
    {
        $this->crdate = $crdate;
        return $this;
    }

    /**
     * @return Linklistener
     */
    public function getLinklistener(): ?Linklistener
    {
        return $this->linklistener;
    }

    /**
     * @param Linklistener $linklistener
     * @return Linkclick
     */
    public function setLinklistener(Linklistener $linklistener): self
    {
        $this->linklistener = $linklistener;
        return $this;
    }

    /**
     * @return Page
     */
    public function getPage(): ?Page
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

    /**
     * @return Visitor
     */
    public function getVisitor(): ?Visitor
    {
        return $this->visitor;
    }

    /**
     * @param Visitor $visitor
     * @return Linkclick
     */
    public function setVisitor(Visitor $visitor): self
    {
        $this->visitor = $visitor;
        return $this;
    }
}
