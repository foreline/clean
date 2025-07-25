<?php
declare(strict_types=1);

namespace App\Infrastructure\DI;

use Psr\Container\ContainerInterface;

interface ServiceProviderInterface
{
    public function register(ContainerInterface $container): void;
}
