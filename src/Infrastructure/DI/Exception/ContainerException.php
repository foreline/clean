<?php
declare(strict_types=1);

namespace Infrastructure\DI\Exception;

use Exception;
use Psr\Container\ContainerExceptionInterface;

/**
 * Container Exception
 * 
 * General exception for container-related errors
 */
class ContainerException extends Exception implements ContainerExceptionInterface
{
}
