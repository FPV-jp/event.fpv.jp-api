<?php

declare(strict_types=1);

ini_set('display_errors', "On");

use Slim\App;
use UMA\DIC\Container;
use UMA\FpvJpApi\DI;
use Slim\Exception\HttpNotFoundException;
use Psr\Log\LoggerInterface;
use UMA\FpvJpApi\DI\MonologLogger;
use UMA\FpvJpApi\FpvJpApi;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

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
 *
 * ルーティング ミドルウェアは、ErrorMiddleware よりも前に追加する必要があります。
 * そうしないと、ルーティング ミドルウェアからスローされた例外がミドルウェアによって処理されません。
*/
$app->addRoutingMiddleware();

/**
 * Catch-all route to serve a 404 Not Found page if none of the routes match
 * NOTE: make sure this route is defined last
 * 
 * どのルートも一致しない場合に 404 Not Found ページを提供するキャッチオール ルート
 * 注: このルートが最後に定義されていることを確認してください
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
 * Note: This middleware should be added last. 
 * It will not handle any exceptions/errors for middleware added after it.
 * 
 * 注: このミドルウェアは最後に追加する必要があります。
 * その後に追加されたミドルウェアの例外/エラーは処理されません。
 */
$logger = new MonologLogger('example_logger', [new StreamHandler(__DIR__.'/app.log', Logger::DEBUG)]);
$errorMiddleware = $app->addErrorMiddleware(true, true, true, $logger);
// $logger->info('This is an informational message.');

$app->run();
