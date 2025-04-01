<?php
declare(strict_types=1);

namespace Tests\Presentation\Helpers;

use DateInterval;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Presentation\Helpers\Dates;

/**
 *
 */
class DatesTest extends TestCase
{
    
    /**
     * @return void
     */
    public function testSecondsOffset(): void
    {
        // Arrange
        $now = new DateTimeImmutable();
        $now = $now->modify('-' . $now->format('u') . ' usec');
        
        $expected = clone $now;
        $expected = $expected->modify('+3600 seconds');
        $expected = $expected->modify('-' . $expected->format('u') . ' usec');
        
        // Act
        $actual = Dates::getDateFromTime('3600');
        $actual = $actual->modify('-' . $actual->format('u') . ' usec');
        
        // Assert
        $this->assertEquals(
            $expected,
            $actual,
            'Should add 3600 seconds to current time'
        );
    }
    
    /**
     * @return void
     */
    public function testSameDayBeforeTargetTime(): void
    {
        // Arrange
        $now = new DateTimeImmutable();
        $now = $now->setTime(8, 59, 59);
        
        $expected = $now->setTime(9, 0, 0);
        
        // Act
        $actual = Dates::getDateFromTime('09:00', $now);
        
        // Assert
        $this->assertEquals(
            $expected,
            $actual,
            "Should return same day when current time is before target"
        );
    }
    
    /**
     * @return void
     */
    public function testSameDayBeforeTargetTimeWithSeconds(): void
    {
        // Arrange
        $now = new DateTimeImmutable();
        $now = $now->setTime(8, 59, 59);
        
        $expected = $now->setTime(9, 0, 0);
        
        // Act
        $actual = Dates::getDateFromTime('09:00:00', $now);
        
        // Assert
        $this->assertEquals(
            $expected,
            $actual,
            "Should return same day when current time is before target"
        );
    }
    
    /**
     *
     */
    public function testNextDayAfterTargetTime(): void
    {
        // Arrange
        $now = new DateTimeImmutable();
        $now = $now->setTime(9, 0, 1);
        
        $expected = $now
            ->add(new DateInterval('P1D'))
            ->setTime(9, 0, 0);
        
        // Act
        $actual = Dates::getDateFromTime('09:00', $now);
        
        // Assert
        $this->assertEquals(
            $expected,
            $actual,
            'Should return next day when current time is after target'
        );
    }
    
    /**
     * @return void
     */
    public function testFromString(): void
    {
        // Arrange
        $input = '3600';
        $now = new DateTimeImmutable();
        
        $expected = $now
            ->add(new DateInterval('PT1H'));
        
        // Act
        $actual = Dates::getDateFromTime($input, $now);
        
        // Assert
        static::assertEquals(
            $expected,
            $actual,
            'Should add 1 hour to current time'
        );
    }
    
    /**
     * @return void
     */
    public function testInvalidInputThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Dates::getDateFromTime('invalid input');
    }
}
