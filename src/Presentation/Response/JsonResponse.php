<?php
    declare(strict_types=1);
    
    namespace Presentation\Response;

    /**
     *
     */
    class JsonResponse
    {
        private string $status;
        
        public function __construct()
        {
        
        }
        
        public function error(): string
        {
            return '';
        }
        
        public function response(): string
        {
            return '';
        }
    
        /**
         * @param string $status
         * @return JsonResponse
         */
        public function setStatus(string $status): JsonResponse
        {
            $this->status = $status;
            return $this;
        }
    
        /**
         * @return string
         */
        public function getStatus(): string
        {
            return $this->status;
        }
    }