<?php
declare(strict_types=1);

namespace Tests\Domain\Helpers;

use Domain\Helpers\Utils;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class UtilsTest extends TestCase
{
    /**
     * @return void
     */
    public function testLcfirst(): void
    {
        // Arrange
        $inputs = [
            // input    expected
            'ID'    => 'ID',
            'Test'  => 'test',
            'test'  => 'test',
            'ABC'   => 'ABC',
            'АБВ'   => 'АБВ',
            'Тест'  => 'Тест',
            'тест'  => 'тест',
            'I2C'   => 'I2C',
        ];
    
        // Act
        foreach ( $inputs as $key => $input ) {
            $result = Utils::lcfirst($key);
        
            // Assert
            static::assertTrue($result === $input, 'Failed asserting that ' . $input . ' equals ' . $result);
        }
    }
    
    /**
     * @return void
     */
    public function testCamelToSnake(): void
    {
        // Arrange
        $inputs = [
            // input    expected
            'camelCase' => 'camel_case',
        ];
        
        // Act
        foreach ( $inputs as $key => $input) {
            $result = Utils::camelToSnake($key);
            
            // Assert
            static::assertEquals($result === $input, 'Failed asserting that ' . $input . ' equals ' . $result);
        }
    }
    
    /**
     * @return void
     */
    public function testSnakeToCamel(): void
    {
        // Arrange
        $inputs = [
            // input                expected
            'snake_case'        => 'snakeCase',
            'snake_case_long'   => 'snakeCaseLong',
            'SNAKE_CASE'        => 'snakeCase',
            'SNAKE_CASE_LONG'   => 'snakeCaseLong',
            //'змеиная_нотация'   => 'змеинаяНотация',
            //'ЗМЕИНАЯ_НОТАЦИЯ'   => 'змеинаяНотация',
        ];
    
        // Act
        foreach ( $inputs as $key => $input) {
            $result = Utils::snakeToCamel($key);
        
            // Assert
            static::assertEquals($result === $input, 'Failed asserting that ' . $input . ' equals ' . $result);
        }
    }
}
