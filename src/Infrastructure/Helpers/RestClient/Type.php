<?php
    declare(strict_types=1);
    
    namespace Infrastructure\Helpers\RestClient;

    use InvalidArgumentException;

    /**
     *
     */
    class Type
    {
        const POST = 'POST';
        const GET = 'GET';
        const PUT = 'PUT';
        const DELETE = 'DELETE';
        
        private array $types = [
            self::POST,
            self::GET,
            self::PUT,
            self::DELETE,
        ];
        
        private string $type;
    
        /**
         * @param string $type
         */
        public function __construct(string $type = self::GET)
        {
            if ( !in_array($type, $this->types) ) {
                throw new InvalidArgumentException('Неверный тип');
            }
            $this->type = $type;
        }
    
        /**
         * @return string
         */
        public function getType(): string
        {
            return $this->type;
        }
    }