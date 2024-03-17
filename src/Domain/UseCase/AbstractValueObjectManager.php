<?php
    declare(strict_types=1);
    
    namespace Domain\UseCase;
    
    use JetBrains\PhpStorm\Deprecated;
    use Webmozart\Assert\Assert;

    /**
     * Родительский класс для объектов-значений (ValueObject)
     */
    abstract class AbstractValueObjectManager
    {
        protected array $_filter = [];
        protected int $_limit = 0;
        protected int $offset = 0;
        protected int $pageNum = 0;
        protected array $_sort = [];
        protected array $_fields = [];
        protected string $listPageUrl = '';
        
        public Filter $filter;
        public Sort $sort;
        public Limit $limit;
        public Fields $fields;
    
        /**
         *
         */
        public function __construct()
        {
            $this->filter = new Filter();
            $this->sort = new Sort();
            $this->limit = new Limit();
            $this->fields = new Fields();
        }
    
        /**
         * @param Filter $filter
         * @return $this
         */
        public function filter(Filter $filter): self
        {
            $this->filter = $filter;
            return $this;
        }
    
        /**
         * @param Sort $sort
         * @return $this
         */
        public function sort(Sort $sort): self
        {
            $this->sort = $sort;
            return $this;
        }
    
        /**
         * @param Limit $limit
         * @return $this
         */
        public function limit(Limit $limit): self
        {
            $this->limit = $limit;
            return $this;
        }
    
        /**
         * @param Fields $fields
         * @return $this
         */
        public function fields(Fields $fields): self
        {
            $this->fields = $fields;
            return $this;
        }
        
        /**
         * @param int $limit
         * @return self
         * @deprecated use $this->limit->setLimit()
         */
        public function setLimit(int $limit = 1): self
        {
            // 0 - no limit
            //Assert::greaterThan($limit, 0, 'Limit must be greater than 0');
            $this->_limit = $limit;
            return $this;
        }
    
        /**
         * @param int $limit
         * @param int $offset
         * @param int $pageNum
         * @return self
         * @deprecated Use $this->limit->set()
         */
        public function setLimits(int $limit = 1, int $offset = 0, int $pageNum = 1): self
        {
            Assert::greaterThan($limit, 0, 'Limit must be greater than 0');
            $this->_limit = $limit;
            $this->offset = $offset;
            $this->pageNum = $pageNum;
            return $this;
        }
    
        /**
         * @return int
         * @deprecated Use $this->limit->getLimit()
         */
        public function getLimit(): int
        {
            return $this->_limit;
        }
    
        /**
         * @param int $offset
         * @return self
         * @deprecated Use $this->limit->setOffset()
         */
        public function setOffset(int $offset): self
        {
            $this->offset = $offset;
            return $this;
        }
    
        /**
         * @return int
         * @deprecated Use $this->limit->getOffset()
         */
        public function getOffset(): int
        {
            return $this->offset;
        }
    
        /**
         * @return int
         * @deprecated Use $this->limit->getPageNum()
         */
        public function getPageNum(): int
        {
            return $this->pageNum;
        }
    
        /**
         * @param int $pageNum
         * @return self
         * @deprecated Use $this->limit->setPageNum()
         */
        public function setPageNum(int $pageNum): self
        {
            Assert::greaterThanEq($pageNum, 1, 'Page number must be greater than 0');
            $this->pageNum = $pageNum;
            return $this;
        }
    
        /**
         * @return array
         * @deprecated Use $this->limit->get()
         */
        public function getLimits(): array
        {
            return [
                'limit'     => $this->_limit,
                'offset'    => $this->offset,
                'page_num'  => $this->pageNum,
            ];
        }
    
        /**
         * @return self
         * @deprecated Use $this->limit->reset()
         */
        protected function resetLimit(): self
        {
            $this->_limit = 0;
            $this->offset = 0;
            return $this;
        }
    
        /**
         * @return $this
         * @deprecated Use $this->sort->byRand()
         */
        public function sortByRand(): self
        {
            $this->_sort = [
                'rand'  => 'asc',
            ];
            return $this;
        }
    
        /**
         * @param string $sort ['asc', 'desc']
         * @return self
         * @deprecated Use $this->sort->byCnt()
         */
        public function sortByCnt(string $sort = 'asc'): self
        {
            $this->_sort = ['cnt' => $sort];
            return $this;
        }
    
        /**
         * Добавляет поле для сортировки
         * @param string $field
         * @param string $order
         * @return self
         * @deprecated Use $this->sort->by()
         */
        public function sortBy(string $field, string $order = 'asc'): self
        {
            $field = mb_strtolower($field);
            $order = mb_strtolower($order);
        
            if ( !in_array($field, $this->_sort) ) {
                $this->_sort[$field] = $order;
            }
        
            return $this;
        }
    
        /**
         * Устанавливает поле для сортировки (сбрасывая текущие значения)
         * @param string $field
         * @param string $order
         * @return self
         * @deprecated Use $this->sort->setSortBy()
         */
        public function setSortBy(string $field, string $order = 'asc'): self
        {
            $field = mb_strtolower($field);
            $order = mb_strtolower($order);
        
            $this->_sort = [];
            if ( !in_array($field, $this->_sort) ) {
                $this->_sort[$field] = $order;
            }
        
            return $this;
        }
    
        /**
         * Добавляет поле для сортировки
         * @param string $field Поле сортировки
         * @param string $order Порядок сортировки asc|desc
         * @return self
         * @deprecated Use $this->sort->add()
         */
        public function addSort(string $field, string $order = 'asc'): self
        {
            $this->_sort[$field] = $order;
            return $this;
        }
    
        /**
         * @param array $sort
         * @return array
         * @deprecated Use $this->sort->get()
         */
        public function getSort(#[Deprecated]array $sort = []): array
        {
            return array_merge($this->_sort, $sort);
        }
    
        /**
         * @param array $sort
         * @return self
         * @deprecated Use $this->sort->set()
         */
        public function setSort(array $sort): self
        {
            foreach ( $sort as $sortBy => $sortOrder )
            {
                $this->sort->by((string)$sortBy, $sortOrder);
            }
            return $this;
        }
    
        /**
         * @return self
         * @deprecated Use $this->sort->reset()
         */
        protected function resetSort(): self
        {
            $this->_sort = [];
            return $this;
        }
    
        /**
         * Задает поля для выборки
         * @param array $fields
         * @return self
         * @deprecated Use $this->fields->set()
         */
        public function setFields(array $fields = []): self
        {
            $this->_fields = $fields;
            return $this;
        }
    
        /**
         * @param array $fields
         * @return array
         * @deprecated Use $this->fields->get()
         */
        public function getFields(#[Deprecated]array $fields = []): array
        {
            return array_merge($this->_fields, $fields);
        }
    
        /**
         * Сбрасывает выбираемые поля
         * @return self
         * @deprecated Use $this->fields->reset()
         */
        public function resetFields(): self
        {
            $this->_fields = [];
            return $this;
        }
    
        /**
         * Сбрасывает параметры фильтрации, сортировки, ограничения выборки и выбираемых полей
         * @return self
         */
        public function reset(): self
        {
            $this
                ->resetFilter()
                ->resetSort()
                ->resetLimit()
                ->resetFields();
            
            $this->filter->reset();
            $this->sort->reset();
            $this->fields->reset();
            $this->limit->reset();
            
            return $this;
        }
    
        /**
         * @return string
         */
        public function getListPageUrl(): string
        {
            return $this->listPageUrl;
        }
    
        /**
         * @param string $listPageUrl
         * @return self
         */
        public function setListPageUrl(string $listPageUrl): self
        {
            $this->listPageUrl = $listPageUrl;
            return $this;
        }
    
        /**
         * Объединяет указанные параметры фильтрации и возвращает суммарный массив
         * @param array $filter
         * @return array
         * @deprecated Use $this->filter->get()
         */
        public function getFilter(#[Deprecated]array $filter = []): array
        {
            return array_merge($this->_filter, $filter);
        }
    
        /**
         * Задает параметры фильтрации
         * @param array $filter
         * @return self
         * @deprecated Use $this->filter->set()
         */
        public function setFilter(array $filter): self
        {
            $this->_filter = $filter;
            return $this;
        }
    
        /**
         * Добавляет параметр фильтрации
         * @param string $field
         * @param $value
         * @return self
         * @deprecated Use $this->filter->add()
         */
        public function addFilter(string $field, $value): self
        {
            $this->_filter[$field] = $value;
            return $this;
        }
    
        /**
         * Сбрасывает параметры фильтрации
         * @return self
         * @deprecated Use $this->filter->reset()
         */
        public function resetFilter(): self
        {
            $this->_filter = [];
            return $this;
        }
    
        /**
         * @param bool|bool[] $active
         * @return self
         * @deprecated Use $this->filter->byActive()
         */
        public function filterByActive(bool|array $active = true): self
        {
            $this->addFilter('active', $active);
            return $this;
        }
    
        /**
         * @param int|int[] $id
         * @return self
         * @deprecated Use $this->filter->byId()
         */
        public function filterById(int|array $id): self
        {
            $this->addFilter('id', $id);
            return $this;
        }
    
        /**
         * Фильтр по ID инициатора или ID инициаторов
         * @param int|int[] $id
         * @return self
         * @deprecated Use $this->filter->byCreator()
         */
        public function filterByCreator(int|array $id): self
        {
            $this->addFilter('created_by', $id);
            return $this;
        }
    
        /**
         * @param string $name
         * @return $this
         * @deprecated Use $this->filter->byName()
         */
        public function filterByName(string $name): self
        {
            $this->addFilter('name', $name);
            return $this;
        }
    
        /**
         * Добавляет условие к фильтру
         * @param array $condition
         * @param string $logic OR|AND
         * @return $this
         * @deprecated Use $this->filter->addCondition()
         */
        public function addCondition(array $condition, string $logic = 'OR'): self
        {
            $condition['LOGIC'] = $logic;
            $this->addFilter('condition', $condition);
            return $this;
        }
    }