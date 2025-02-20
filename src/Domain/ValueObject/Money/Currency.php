<?php
declare(strict_types=1);

namespace Domain\ValueObject\Money;

use Domain\ValueObject\StringValueObjectInterface;
use InvalidArgumentException;

/**
 *
 */
class Currency implements StringValueObjectInterface
{
    private const DEFAULT_CURRENCY_CODE = 'RUB';
    
    private string $name;
    private string $code;
    private int $numericCode;
    private int $scale;
    private string $shortHand;
    
    /**
     * @param string $currencyCode
     * @throws InvalidArgumentException
     */
    public function __construct(string $currencyCode = self::DEFAULT_CURRENCY_CODE)
    {
        $currencyCode = mb_strtoupper(trim($currencyCode));
        
        if ( empty($currencyCode) ) {
            $currencyCode = self::DEFAULT_CURRENCY_CODE;
        }
        
        if ( 3 !== strlen($currencyCode) ) {
            throw new InvalidArgumentException('Invalid currency code "' . $currencyCode . '". The code must consist of three characters');
        }
        
        $currencyClass = __NAMESPACE__ . '\Currency\\' . $currencyCode;
        
        if ( !class_exists($currencyClass) ) {
            throw new InvalidArgumentException('Currency with code "' . $currencyCode . '" not found');
        }
        
        /** @var CurrencyInterface $currency */
        $currency = new $currencyClass;
        
        $this->name = $currency->getName();
        $this->code = $currency->getCode();
        $this->numericCode = $currency->getNumericCode();
        $this->scale = $currency->getScale();
        $this->shortHand = $currency->getShorthand();
    }
    
    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }
    
    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
    
    /**
     * @return int
     */
    public function getNumericCode(): int
    {
        return $this->numericCode;
    }
    
    /**
     * @return int
     */
    public function getScale(): int
    {
        return $this->scale;
    }
    
    /**
     * @return string
     */
    public function getShorthand(): string
    {
        return $this->shortHand;
    }
    
    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getCode();
    }
    
    /**
     * @return Currency[]
     */
    public static function getAll(): array
    {
        return [
            new self('RUB'),
            new self('USD'),
            new self('BYN'),
            new self('EUR'),
            new self('CNY'),
        ];
    }
}