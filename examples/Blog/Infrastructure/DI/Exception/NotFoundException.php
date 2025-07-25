<?php
declare(strict_types=1);

namespace App\Infrastructure\DI\Exception;

use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends \Exception implements NotFoundExceptionInterface
{
}
