<?php
declare(strict_types=1);

namespace Domain\UseCase;

/**
 * Parent class for Entity Manager
 */
abstract class AbstractManager extends AbstractValueObjectManager
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
    }
}
