<?php
declare(strict_types=1);

namespace Domain\Event\Enum;

/**
 * Status of an event in the EventStore.
 *
 * Represents the lifecycle state of an async event message
 * from initial persistence through processing to completion.
 */
enum EventStoreStatusEnum: string
{
    /** Ожидает обработки */
    case PENDING = 'pending';
    
    /** Обрабатывается */
    case PROCESSING = 'processing';
    
    /** Успешно обработано */
    case COMPLETED = 'completed';
    
    /** Ошибка обработки */
    case FAILED = 'failed';
    
    /**
     * @return string
     */
    public function name(): string
    {
        return match ($this) {
            self::PENDING => 'Ожидает обработки',
            self::PROCESSING => 'Обрабатывается',
            self::COMPLETED => 'Завершено',
            self::FAILED => 'Ошибка',
        };
    }
    
    /**
     * @return string
     */
    public function description(): string
    {
        return match ($this) {
            self::PENDING => 'Событие ожидает обработки',
            self::PROCESSING => 'Событие в данный момент обрабатывается',
            self::COMPLETED => 'Событие успешно обработано подписчиком',
            self::FAILED => 'При обработке события произошла ошибка',
        };
    }
}
