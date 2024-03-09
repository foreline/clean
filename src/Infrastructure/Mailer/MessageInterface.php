<?php
    
    namespace Infrastructure\Mailer;

    /**
     * Интерфейс сообщения
     */
    interface MessageInterface
    {
        /**
         * @param string $to
         * @return $this
         */
        public function setTo(string $to): self;
    
        /**
         * @return string
         */
        public function getTo(): string;
    
        /**
         * @param string $from
         * @return $this
         */
        public function setFrom(string $from): self;
    
        /**
         * @return string
         */
        public function getFrom(): string;
    
        /**
         * @param string $subject
         * @return $this
         */
        public function setSubject(string $subject): self;
    
        /**
         * @return string
         */
        public function getSubject(): string;
    
        /**
         * @param string $body
         * @return $this
         */
        public function setBody(string $body): self;
    
        /**
         * @return string
         */
        public function getBody(): string;
        
    }