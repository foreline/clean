<?php
declare(strict_types=1);

namespace Domain\ValueObject\Money;

use Domain\ValueObject\MultipleValueObjectInterface;
use Domain\ValueObject\ValueObjectInterface;
use InvalidArgumentException;

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
    public function __construct(float $amount, string $currencyCode = '')
    {
        $this->amount = (int) round($amount * self::MULTIPLIER);
        $this->currency = new Currency($currencyCode);
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
     * @param Money $money
     * @return Money
     */
    public function plus(Money $money): Money
    {
        if ( $money->getCurrency()->getCode() !== $this->getCurrency()->getCode() ) {
            throw new InvalidArgumentException('Валюта не соответствует');
        }
        
        $sumAmount = $this->getAmount() + $money->getAmount();
        return new self($sumAmount, $this->getCurrency()->getCode());
    }
    
    /**
     * @return Money
     */
    public function invert(): Money
    {
        return new self(-1 * $this->getAmount(), $this->getCurrency()->getCode());
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
        return
            number_format($this->getAmount(), 0, '.', '&nbsp;') .
            '&nbsp;' .
            ( $this->getCurrency()->getSymbol() ?: $this->getCurrency()->getShorthand() ?: $this->getCurrency()->getCode() );
    }
}