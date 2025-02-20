<?php
declare(strict_types=1);

namespace Domain\ValueObject\Money;

use Domain\ValueObject\MultipleValueObjectInterface;
use Domain\ValueObject\ValueObjectInterface;

/**
 *
 */
class Money implements MultipleValueObjectInterface
{
    /** @var int Number of decimal places for money */
    private const DECIMAL_PLACES = 2;
    
    /** @var int Multiplier. 100 for 2 decimal places */
    private const MULTIPLIER = 10 ** self::DECIMAL_PLACES;
    
    private int $amount;
    private Currency $currency;
    
    /**
     * @param float $amount
     * @param string $currencyCode
     */
    //public function __construct(float $amount, Currency $currency)
    public function __construct(float $amount, string $currencyCode = '')
    {
        $this->amount = (int) round($amount * self::MULTIPLIER);
        $this->currency = (new Currency($currencyCode));
    }
    
    /**
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount / self::MULTIPLIER;
    }
    
    /**
     * @return Currency
     */
    public function getCurrency(): Currency
    {
        return $this->currency;
    }
    
    /**
     * @return array|ValueObjectInterface[]
     */
    public static function getAll(): array
    {
        return [];
    }
    
    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getAmount() . ' ' . $this->getCurrency()->getShorthand();
    }
}