<?php
declare(strict_types=1);

namespace Domain\ValueObject\IPv4;

use Domain\ValueObject\IntValueObjectInterface;
use Domain\ValueObject\StringValueObjectInterface;
use InvalidArgumentException;

/**
 *
 */
class IPv4 implements IntValueObjectInterface, StringValueObjectInterface
{
    private int $ip;
    
    /**
     * @param string $address
     * @throws InvalidArgumentException
     */
    public function __construct(string $address)
    {
        if ( !filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ) {
            throw new InvalidArgumentException('Invalid IPv4 address: "' . $address . '"');
        }
        
        $this->ip = ip2long($address);
    }
    
    /**
     * @return bool
     */
    public function isLocalHost(): bool
    {
        return ($this->ip & 0xFF000000) === 0x7F000000;
    }
    
    /**
     * @return bool
     */
    public function isPrivate(): bool
    {
        return (filter_var($this->ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false);
    }
    
    /**
     * @return int
     */
    public function getIp(): int
    {
        return $this->ip;
    }
    
    /**
     * @return string
     */
    public function __toString(): string
    {
        return long2ip($this->ip);
    }
}