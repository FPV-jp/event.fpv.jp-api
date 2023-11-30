<?php

declare(strict_types=1);

ini_set('display_errors', "On");

use Slim\App;
use UMA\DIC\Container;
use UMA\FpvJpApi\DI;
use Slim\Exception\HttpNotFoundException;

/** @var Container $cnt */
$cnt = require_once __DIR__ . '/../bootstrap.php';

$cnt->register(new DI\Doctrine());
$cnt->register(new DI\Slim());

/** @var App $app */
$app = $cnt->get(App::class);

$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

/**
 * The routing middleware should be added earlier than the ErrorMiddleware
* Otherwise exceptions thrown from it will not be handled by the middleware
*/
$app->addRoutingMiddleware();

/**
 * Catch-all route to serve a 404 Not Found page if none of the routes match
 * NOTE: make sure this route is defined last
 */
$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function ($request, $response) {
    throw new HttpNotFoundException($request);
});

/**
 * Add Error Middleware
 *
 * @param bool                  $displayErrorDetails -> Should be set to false in production
 * @param bool                  $logErrors -> Parameter is passed to the default ErrorHandler
 * @param bool                  $logErrorDetails -> Display error details in error log
 * @param LoggerInterface|null  $logger -> Optional PSR-3 Logger  
 *
 * Note: This middleware should be added last. It will not handle any exceptions/errors
 * for middleware added after it.
 */
$errorMiddleware = $app->addErrorMiddleware(true, true, true);
  
$app->run();
