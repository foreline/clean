<?php
declare(strict_types=1);

namespace Infrastructure\DI\Exception;

use Exception;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Service Not Found Exception
 * 
 * Thrown when a requested service is not registered in the container
 */
class NotFoundException extends Exception implements NotFoundExceptionInterface
{
}
