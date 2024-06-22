<?php
    declare(strict_types=1);
    
    namespace Domain\UseCase;

    /**
     *
     */
    class Sort
    {
        /** @var array<string,string>  */
        private array $sort = [];
    
        /**
         * @return self
         */
        public function byRand(): self
        {
            $this->sort = [
                'rand'  => 'asc',
            ];
            return $this;
        }
    
        /**
         * @param string $sort ['asc', 'desc']
         * @return self
         */
        public function byCnt(string $sort = 'asc'): self
        {
            $this->sort = ['cnt' => $sort];
            return $this;
        }
    
        /**
         * Добавляет поле для сортировки
         * @param string $field
         * @param string $order
         * @return self
         */
        public function by(string $field, string $order = 'asc'): self
        {
            $field = mb_strtolower($field);
            $order = mb_strtolower($order);
        
            if ( !in_array($field, $this->sort) ) {
                $this->sort[$field] = $order;
            }
        
            return $this;
        }
    
        /**
         * Устанавливает поле для сортировки (сбрасывая текущие значения)
         * @param string $field
         * @param string $order
         * @return self
         */
        public function setSortBy(string $field, string $order = 'asc'): self
        {
            $field = mb_strtolower($field);
            $order = mb_strtolower($order);
        
            $this->sort = [];
            if ( !in_array($field, $this->sort) ) {
                $this->sort[$field] = $order;
            }
        
            return $this;
        }
    
        /**
         * Добавляет поле для сортировки
         * @param string $field Поле сортировки
         * @param string $order Порядок сортировки asc|desc
         * @return self
         */
        public function add(string $field, string $order = 'asc'): self
        {
            $this->sort[$field] = $order;
            return $this;
        }
    
        /**
         * @return array<string,string>
         */
        public function get(): array
        {
            return $this->sort;
        }
    
        /**
         * @param array<string,string> $sort
         * @return self
         */
        public function set(array $sort): self
        {
            /*foreach ( $sort as $tsort ) {
                //list($field, $order) = $torder;
                //[$field => $order] = $torder;
                foreach ( $tsort as $field => $order ) {
                    $this->sortBy($field, $order);
                }
            }*/
            foreach ( $sort as $sortBy => $sortOrder )
            {
                $this->by((string)$sortBy, $sortOrder);
            }
            return $this;
        }
    
        /**
         * @return self
         */
        public function reset(): self
        {
            $this->sort = [];
            return $this;
        }
    }