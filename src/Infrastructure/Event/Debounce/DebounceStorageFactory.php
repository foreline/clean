<?php
declare(strict_types=1);

namespace Infrastructure\Event\Debounce;

use Domain\Event\DebounceStorageInterface;
use InvalidArgumentException;
use Memcached;
use Redis;
use RuntimeException;

/**
 * Builds a {@see DebounceStorageInterface} from environment configuration.
 *
 * Driver selection and connection details are read from environment variables
 * (typically populated from a `.env` file), so the backend can be switched
 * without code changes.
 *
 * Recognised variables:
 *  - EVENT_DEBOUNCE_STORAGE            file | redis | memcached   (default: file)
 *
 *  File driver:
 *  - EVENT_DEBOUNCE_FILE_PATH          storage directory          (default: sys_temp/event-debounce)
 *
 *  Redis driver (requires ext-redis):
 *  - EVENT_DEBOUNCE_REDIS_HOST         (default: 127.0.0.1)
 *  - EVENT_DEBOUNCE_REDIS_PORT         (default: 6379)
 *  - EVENT_DEBOUNCE_REDIS_AUTH         optional password
 *  - EVENT_DEBOUNCE_REDIS_PREFIX       (default: debounce)
 *
 *  Memcached driver (requires ext-memcached):
 *  - EVENT_DEBOUNCE_MEMCACHED_HOST     (default: 127.0.0.1)
 *  - EVENT_DEBOUNCE_MEMCACHED_PORT     (default: 11211)
 *  - EVENT_DEBOUNCE_MEMCACHED_PREFIX   (default: debounce)
 */
final class DebounceStorageFactory
{
    /**
     * Creates a storage backend from the given environment map (defaults to $_ENV).
     *
     * @param array<string, mixed>|null $env
     * @return DebounceStorageInterface
     * @throws InvalidArgumentException If the configured driver is unknown.
     * @throws RuntimeException If the required extension is missing.
     */
    public static function fromEnv(?array $env = null): DebounceStorageInterface
    {
        $env ??= $_ENV;
        $driver = strtolower(trim((string)($env['EVENT_DEBOUNCE_STORAGE'] ?? 'file')));

        return match ( $driver ) {
            '', 'file'   => self::createFile($env),
            'redis'      => self::createRedis($env),
            'memcached'  => self::createMemcached($env),
            default      => throw new InvalidArgumentException("Unknown debounce storage driver: {$driver}"),
        };
    }

    /**
     * @param array<string, mixed> $env
     * @return DebounceStorageInterface
     */
    private static function createFile(array $env): DebounceStorageInterface
    {
        $directory = (string)($env['EVENT_DEBOUNCE_FILE_PATH']
            ?? sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'event-debounce');

        return new FileDebounceStorage($directory);
    }

    /**
     * @param array<string, mixed> $env
     * @return DebounceStorageInterface
     * @throws RuntimeException
     */
    private static function createRedis(array $env): DebounceStorageInterface
    {
        if ( !class_exists(Redis::class) ) {
            throw new RuntimeException('The "redis" PHP extension is required for the redis debounce storage.');
        }

        $redis = new Redis();
        $redis->connect(
            (string)($env['EVENT_DEBOUNCE_REDIS_HOST'] ?? '127.0.0.1'),
            (int)($env['EVENT_DEBOUNCE_REDIS_PORT'] ?? 6379),
        );

        if ( !empty($env['EVENT_DEBOUNCE_REDIS_AUTH']) ) {
            $redis->auth((string)$env['EVENT_DEBOUNCE_REDIS_AUTH']);
        }

        return new RedisDebounceStorage(
            $redis,
            (string)($env['EVENT_DEBOUNCE_REDIS_PREFIX'] ?? 'debounce'),
        );
    }

    /**
     * @param array<string, mixed> $env
     * @return DebounceStorageInterface
     * @throws RuntimeException
     */
    private static function createMemcached(array $env): DebounceStorageInterface
    {
        if ( !class_exists(Memcached::class) ) {
            throw new RuntimeException('The "memcached" PHP extension is required for the memcached debounce storage.');
        }

        $memcached = new Memcached();
        $memcached->addServer(
            (string)($env['EVENT_DEBOUNCE_MEMCACHED_HOST'] ?? '127.0.0.1'),
            (int)($env['EVENT_DEBOUNCE_MEMCACHED_PORT'] ?? 11211),
        );

        return new MemcachedDebounceStorage(
            $memcached,
            (string)($env['EVENT_DEBOUNCE_MEMCACHED_PREFIX'] ?? 'debounce'),
        );
    }
}
