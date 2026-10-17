<?php
declare(strict_types=1);

namespace Tests\Domain;

use Domain\Event\DebounceState;
use Domain\User\Entity\UserEntity;
use Domain\ValueObject\Color\Color;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Регрессии стандартной валидации без сторонних assertions.
 * @covers \Domain\Event\DebounceState
 * @covers \Domain\User\Entity\UserEntity
 * @covers \Domain\ValueObject\Color\Color
 */
final class ValidationWithoutAssertTest extends TestCase
{
    public function testDebounceRoundTripAndMissingKeys(): void
    {
        // Arrange: строка "0" допустима, в отличие от empty().
        $state = new DebounceState('0', 'Subscriber', 'Event', '', 0, 0, 0);
        $data = $state->toArray();
        // Act / Assert
        self::assertSame($data, DebounceState::fromArray($data)->toArray());
        foreach (array_keys($data) as $key) {
            $invalid = $data;
            unset($invalid[$key]);
            try {
                DebounceState::fromArray($invalid);
                self::fail('Missing key accepted: ' . $key);
            } catch (InvalidArgumentException $error) {
                self::assertSame(InvalidArgumentException::class, $error::class);
                self::assertStringContainsString($key, $error->getMessage());
            }
        }
        $data['deadlineMs'] = null;
        self::assertSame(0, DebounceState::fromArray($data)->getDeadlineMs());
    }

    public function testDebounceRejectsEmptyIdentityStrings(): void
    {
        // Arrange / Act / Assert
        foreach ([['', 'Subscriber', 'Event'], ['key', '', 'Event'], ['key', 'Subscriber', '']] as $identity) {
            try {
                new DebounceState($identity[0], $identity[1], $identity[2], '', 0, 0, 0);
                self::fail('Empty identity accepted');
            } catch (InvalidArgumentException $error) {
                self::assertSame(InvalidArgumentException::class, $error::class);
            }
        }
    }

    public function testUserEmailValidationPreservesState(): void
    {
        // Arrange
        $user = new UserEntity();
        foreach (['', 'name+tag@example.test'] as $email) {
            self::assertSame($user, $user->setEmail($email));
            self::assertSame($email, $user->getEmail());
        }
        // Act / Assert
        foreach (['invalid', 'name@example', ' name@example.test', 'имя@example.test'] as $email) {
            try {
                $user->setEmail($email);
                self::fail('Invalid email accepted');
            } catch (InvalidArgumentException $error) {
                self::assertSame(InvalidArgumentException::class, $error::class);
                self::assertSame('Неверный е-мейл', $error->getMessage());
                self::assertSame('name+tag@example.test', $user->getEmail());
            }
        }
    }

    public function testColorBoundariesAndRequiredKeys(): void
    {
        // Arrange
        $color = new Color('#00ff00ff');
        // Act / Assert
        self::assertSame([0, 255, 0, 255], [$color->getRed(), $color->getGreen(), $color->getBlue(), $color->getAlpha()]);
        $data = ['red' => 0, 'green' => 255, 'blue' => null];
        self::assertSame('#00ff00', (string)$color->fromArray($data));
        foreach (array_keys($data) as $key) {
            $invalid = $data;
            unset($invalid[$key]);
            try {
                $color->fromArray($invalid);
                self::fail('Missing color channel accepted');
            } catch (InvalidArgumentException $error) {
                self::assertSame(InvalidArgumentException::class, $error::class);
                self::assertSame('Ключ "' . $key . '" должен присутствовать в массиве', $error->getMessage());
            }
        }
    }
}
