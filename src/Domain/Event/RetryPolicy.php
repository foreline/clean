<?php
declare(strict_types=1);

namespace Domain\Event;

use Exception;

/**
 * Value Object representing a retry policy for async event dispatch.
 *
 * Defines the retry behavior when an async subscriber fails:
 * - How many times to retry (0 = unlimited)
 * - Exponential backoff parameters (base delay, multiplier, max delay)
 * - Jitter to prevent thundering herd when a service recovers
 *
 * Default: unlimited retries, 60s base delay, 2× multiplier,
 * 1-hour max delay, 30s jitter.
 */
final class RetryPolicy
{
    /** @var int Максимальное количество попыток (0 = без ограничений) */
    private int $maxRetries;
    
    /** @var int Базовая задержка в миллисекундах */
    private int $baseDelayMs;
    
    /** @var float Множитель для экспоненциальной задержки */
    private float $multiplier;
    
    /** @var int Максимальная задержка в миллисекундах */
    private int $maxDelayMs;
    
    /** @var int Случайный разброс в миллисекундах (защита от thundering herd) */
    private int $jitterMs;
    
    /**
     * @param int $maxRetries Maximum retry attempts (0 = unlimited)
     * @param int $baseDelayMs Base delay in milliseconds (default: 60000 = 60s)
     * @param float $multiplier Delay multiplier per retry (default: 2.0)
     * @param int $maxDelayMs Maximum delay cap in milliseconds (default: 3600000 = 1 hour)
     * @param int $jitterMs Random jitter in milliseconds (default: 30000 = 30s)
     */
    public function __construct(
        int $maxRetries = 0,
        int $baseDelayMs = 60000,
        float $multiplier = 2.0,
        int $maxDelayMs = 3600000,
        int $jitterMs = 30000,
    ) {
        $this->maxRetries = $maxRetries;
        $this->baseDelayMs = $baseDelayMs;
        $this->multiplier = $multiplier;
        $this->maxDelayMs = $maxDelayMs;
        $this->jitterMs = $jitterMs;
    }
    
    /**
     * Calculates the delay in milliseconds before the next retry attempt.
     *
     * Uses exponential backoff with jitter:
     *   delay = min(baseDelay × multiplier^(attempts-1), maxDelay) + random(0, jitter)
     *
     * @param int $attempts Number of attempts already made (1-based)
     * @return int Delay before next retry in milliseconds
     * @throws Exception
     */
    public function calculateDelayMs(int $attempts): int
    {
        $delay = (int)min(
            $this->baseDelayMs * ($this->multiplier ** ($attempts - 1)),
            $this->maxDelayMs
        );
        
        $jitter = 0 < $this->jitterMs
            ? random_int(0, $this->jitterMs)
            : 0;
        
        return $delay + $jitter;
    }
    
    /**
     * Determines whether the dispatch should be retried based on the attempt count.
     *
     * @param int $attempts Number of attempts already made
     * @return bool True if retry is allowed
     */
    public function shouldRetry(int $attempts): bool
    {
        // 0 = unlimited retries
        if ( 0 === $this->maxRetries ) {
            return true;
        }
        
        return $attempts < $this->maxRetries;
    }
    
    /**
     * @return int
     */
    public function getMaxRetries(): int
    {
        return $this->maxRetries;
    }
    
    /**
     * @return int
     */
    public function getBaseDelayMs(): int
    {
        return $this->baseDelayMs;
    }
    
    /**
     * @return float
     */
    public function getMultiplier(): float
    {
        return $this->multiplier;
    }
    
    /**
     * @return int
     */
    public function getMaxDelayMs(): int
    {
        return $this->maxDelayMs;
    }
    
    /**
     * @return int
     */
    public function getJitterMs(): int
    {
        return $this->jitterMs;
    }
}
