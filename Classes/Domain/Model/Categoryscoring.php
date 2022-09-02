<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Model;

/**
 * Class Categoryscoring
 */
class Categoryscoring extends AbstractModel
{
    const TABLE_NAME = 'tx_lux_domain_model_categoryscoring';

    /**
     * @var int
     */
    protected $scoring = 0;

    /**
     * @var \In2code\Lux\Domain\Model\Category
     */
    protected $category = null;

    /**
     * @var \In2code\Lux\Domain\Model\Visitor
     */
    protected $visitor = null;

    /**
     * @return int
     */
    public function getScoring(): int
    {
        return $this->scoring;
    }

    /**
     * @param int $scoring
     * @return Categoryscoring
     */
    public function setScoring(int $scoring)
    {
        $this->scoring = $scoring;
        return $this;
    }

    /**
     * @return Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param Category $category
     * @return Categoryscoring
     */
    public function setCategory(Category $category)
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @return Visitor
     */
    public function getVisitor(): Visitor
    {
        return $this->visitor;
    }

    /**
     * @param Visitor $visitor
     * @return Categoryscoring
     */
    public function setVisitor(Visitor $visitor)
    {
        $this->visitor = $visitor;
        return $this;
    }
}
