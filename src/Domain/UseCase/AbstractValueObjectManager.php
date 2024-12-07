<?php
declare(strict_types=1);

namespace Domain\UseCase;

use Domain\Repository\Fields;
use Domain\Repository\FieldsInterface;
use Domain\Repository\Filter;
use Domain\Repository\FilterInterface;
use Domain\Repository\Group;
use Domain\Repository\GroupInterface;
use Domain\Repository\Limit;
use Domain\Repository\LimitInterface;
use Domain\Repository\Sort;
use Domain\Repository\SortInterface;

/**
 * Parent class for Value-Objects
 */
abstract class AbstractValueObjectManager
{
    protected int $offset = 0;
    protected int $pageNum = 0;
    protected string $listPageUrl = '';
    
    public FilterInterface $filter;
    public SortInterface $sort;
    public LimitInterface $limit;
    public FieldsInterface $fields;
    public GroupInterface $group;

    /**
     *
     */
    public function __construct()
    {
        $this->filter   = new Filter();
        $this->sort     = new Sort();
        $this->limit    = new Limit();
        $this->fields   = new Fields();
        $this->group    = new Group();
    }

    /**
     * @param FilterInterface $filter
     * @return $this
     */
    public function filter(FilterInterface $filter): self
    {
        $this->filter = $filter;
        return $this;
    }

    /**
     * @param SortInterface $sort
     * @return $this
     */
    public function sort(SortInterface $sort): self
    {
        $this->sort = $sort;
        return $this;
    }

    /**
     * @param Limit $limit
     * @return $this
     */
    public function limit(LimitInterface $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @param Fields $fields
     * @return $this
     */
    public function fields(FieldsInterface $fields): self
    {
        $this->fields = $fields;
        return $this;
    }
    
    /**
     * @param GroupInterface $group
     * @return $this
     */
    public function group(GroupInterface $group): self
    {
        $this->group = $group;
        return $this;
    }

    /**
     * Сбрасывает параметры фильтрации, сортировки, ограничения выборки, выбираемых полей и группировки
     * @return self
     */
    public function reset(): self
    {
        $this->filter->reset();
        $this->sort->reset();
        $this->fields->reset();
        $this->limit->reset();
        $this->group->reset();
        
        return $this;
    }

    /**
     * @return string
     * @deprecated
     */
    public function getListPageUrl(): string
    {
        return $this->listPageUrl;
    }

    /**
     * @param string $listPageUrl
     * @return self
     * @deprecated
     */
    public function setListPageUrl(string $listPageUrl): self
    {
        $this->listPageUrl = $listPageUrl;
        return $this;
    }
}