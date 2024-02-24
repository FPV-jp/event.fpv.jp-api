<?php declare(strict_types=1);

namespace FpvJp\Handler;

use Slim\Handlers\ErrorHandler;

class DefaultErrorHandler extends ErrorHandler
{
    protected function logError(string $error): void
    {
        // Insert custom error logging function.
    }
}