<?php
declare(strict_types=1);

namespace Domain\ValueObject\Color;

use Domain\Entity\FromArrayInterface;
use Domain\Entity\ToArrayInterface;
use Domain\ValueObject\StringValueObjectInterface;
use Domain\ValueObject\ValueObjectInterface;
use Exception;
use Webmozart\Assert\Assert;

/**
 * Color ValueObject
 */
class Color implements ValueObjectInterface, StringValueObjectInterface, ToArrayInterface, FromArrayInterface
{
    private int $red;
    private int $green;
    private int $blue;
    private int $alpha;
    
    /**
     * @param string $color
     * @throws Exception;
     */
    public function __construct(string $color = '')
    {
        $hexColor = static::convertColor($color);
        
        [$red, $green, $blue, $alpha] = sscanf($hexColor, '#%02x%02x%02x%02x');
        
        Assert::greaterThanEq($red, 0, 'Значение красного цвета не может быть отрицательным');
        Assert::lessThanEq($red, 255, 'Значение красного цвета не может превышать 255');
        Assert::greaterThanEq($green, 0, 'Значение зеленого цвета не может быть отрицательным');
        Assert::lessThanEq($green, 255, 'Значение зеленого цвета не может превышать 255');
        Assert::greaterThanEq($blue, 0, 'Значение синего цвета не может быть отрицательным');
        Assert::lessThanEq($blue, 255, 'Значение синего цвета не может превышать 255');
        
        if ( null !== $alpha ) {
            Assert::greaterThanEq($alpha, 0, 'Значение прозрачности не может быть отрицательным');
            //Assert::lessThanEq($alpha, 100, 'Значение прозрачности не может превышать 100');
            Assert::lessThanEq($alpha, 255, 'Значение прозрачности не может превышать 255');
        } else {
            $alpha = 100;
        }
        
        $this->red      = (int)$red;
        $this->green    = (int)$green;
        $this->blue     = (int)$blue;
        $this->alpha    = (int)$alpha;
    }
    
    /**
     * @return int
     */
    public function getRed(): int
    {
        return $this->red;
    }
    
    /**
     * @return int
     */
    public function getGreen(): int
    {
        return $this->green;
    }
    
    /**
     * @return int
     */
    public function getBlue(): int
    {
        return $this->blue;
    }
    
    /**
     * @return int
     */
    public function getAlpha(): int
    {
        return $this->alpha;
    }
    
    /**
     * Возвращает объект случайного цвета
     * @return $this
     * @throws Exception
     */
    public static function getRandom(): self
    {
        $red = mt_rand(0, 255);
        $green = mt_rand(0, 255);
        $blue = mt_rand(0, 255);
        $alpha = mt_rand(0, 100);
        
        $hexColor =  '#'
            . str_pad(dechex($red), 2, '0', STR_PAD_LEFT)
            . str_pad(dechex($green), 2, '0', STR_PAD_LEFT)
            . str_pad(dechex($blue), 2, '0', STR_PAD_LEFT)
            . str_pad(dechex($alpha), 2, '0', STR_PAD_LEFT);
        
        return new static($hexColor);
    }
    
    /**
     * Конвертирует заданный цвет в hex представление
     * @param string $color
     * @return string $hexColor
     */
    public static function convertColor(string $color = ''): string
    {
        return static::rgbaToHex($color);
    }
    
    /**
     * Конвертирует rgba(0, 100, 255, .5) в #00ffff80
     * @param string $rgbaColor
     * @return string
     */
    public static function rgbaToHex(string $rgbaColor = ''): string
    {
        if ( !str_starts_with($rgbaColor, 'rgb') ) {
            return $rgbaColor;
        }
        
        preg_match_all('/([\\d.]+)/', $rgbaColor, $matches);
        
        return sprintf(
            '#%02X%02X%02X%02X',
            $matches[1][0], // red
            $matches[1][1], // green
            $matches[1][2], // blue
            round($matches[1][3] * 255) // adjusted opacity
        );
    }
    
    /**
     * @return string
     */
    public function __toString(): string
    {
        return '#'
            . str_pad(dechex($this->red), 2, '0', STR_PAD_LEFT)
            . str_pad(dechex($this->green), 2, '0', STR_PAD_LEFT)
            . str_pad(dechex($this->blue), 2, '0', STR_PAD_LEFT);
        //. str_pad(dechex($this->alpha), 2, '0', STR_PAD_LEFT);
    }
    
    /**
     * @return array|ValueObjectInterface[]
     */
    public static function getAll(): array
    {
        // @fixme
        return [new self('')];
    }
    
    /**
     * Returns array presentation of Color
     *
     * @param array $fields
     * @return ?array
     */
    public function toArray(array $fields = []): ?array
    {
        return [
            'red'   => $this->getRed(),
            'green' => $this->getGreen(),
            'blue'  => $this->getBlue(),
            'alfa'  => $this->getAlpha(),
        ];
    }
    
    /**
     * Restores Color from array presentation
     *
     * @param array $data
     * @return Color
     * @throws Exception
     */
    public function fromArray(array $data = []): self
    {
        // Проверяем наличие необходимых ключей в массиве
        Assert::keyExists($data, 'red', 'Ключ "red" должен присутствовать в массиве');
        Assert::keyExists($data, 'green', 'Ключ "green" должен присутствовать в массиве');
        Assert::keyExists($data, 'blue', 'Ключ "blue" должен присутствовать в массиве');
    
        // Получаем значения из массива
        $red    = (int)$data['red'];
        $green  = (int)$data['green'];
        $blue   = (int)$data['blue'];
        $alpha  = isset($data['alfa']) ? (int)$data['alfa'] : 100;
    
        // Return Color object
        return new self(sprintf(
            '#%02x%02x%02x%02x',
            $red,
            $green,
            $blue,
            $alpha
        ));
    }
    
}