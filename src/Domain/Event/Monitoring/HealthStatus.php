<?php
declare(strict_types=1);

namespace Domain\Event\Monitoring;

/**
 * Health status of the Event Store.
 */
enum HealthStatus: string
{
    /** Все системы работают нормально */
    case HEALTHY = 'healthy';
    
    /** Обнаружены проблемы, требующие внимания */
    case DEGRADED = 'degraded';
    
    /** Критические проблемы, требующие немедленного вмешательства */
    case CRITICAL = 'critical';
    
    /**
     * @return string
     */
    public function name(): string
    {
        return match ($this) {
            self::HEALTHY  => 'Здоров',
            self::DEGRADED => 'Деградация',
            self::CRITICAL => 'Критический',
        };
    }
    
    /**
     * @return string
     */
    public function description(): string
    {
        return match ($this) {
            self::HEALTHY  => 'Все системы работают нормально',
            self::DEGRADED => 'Обнаружены проблемы, требующие внимания',
            self::CRITICAL => 'Критические проблемы, требующие немедленного вмешательства',
        };
    }
}
