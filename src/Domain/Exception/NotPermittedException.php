<?php
declare(strict_types=1);

namespace Domain\Exception;

use Exception, Throwable;

/**
 * Not Permitted Exception
 */
class NotPermittedException extends Exception
{
    /**
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message = 'Нет доступа', int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}