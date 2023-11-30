<?php

declare(strict_types=1);

namespace UMA\FpvJpApi\DI;

use Doctrine\ORM\EntityManager;
use Faker;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteContext;
use Slim\Middleware\ContentLengthMiddleware;
use UMA\DIC\Container;
use UMA\DIC\ServiceProvider;

use UMA\FpvJpApi\DI\PermissionMiddleware;
use UMA\FpvJpApi\Action\CreateUser;
use UMA\FpvJpApi\Action\ListUsers;

/**
 * A ServiceProvider for registering services related
 * to Slim such as request handlers, routing and the
 * App service itself that wires everything together.
 */
final class Slim implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function provide(Container $c): void
    {
        $c->set(ListUsers::class, static function (ContainerInterface $c): RequestHandlerInterface {
            return new ListUsers($c->get(EntityManager::class));
        });

        $c->set(CreateUser::class, static function (ContainerInterface $c): RequestHandlerInterface {
            return new CreateUser($c->get(EntityManager::class), Faker\Factory::create());
        });

        $c->set(App::class, static function (ContainerInterface $ci): App {

            /** @var array $settings */
            $settings = $ci->get('settings');

            $app = AppFactory::create(null, $ci);

            $app->addErrorMiddleware(
                $settings['slim']['displayErrorDetails'],
                $settings['slim']['logErrors'],
                $settings['slim']['logErrorDetails']
            );

            $app->add(new ContentLengthMiddleware());

            // $app->add(function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
            //     return $handler->handle($request);
            // });

            $app->get('/api/hello/{name}', function (ServerRequestInterface $request, ResponseInterface $response, $args) {
                $routeContext = RouteContext::fromRequest($request);
                $basePath = $routeContext->getBasePath();
                $name = $args['name'];
                $params = $request->getServerParams();
                $authorization = $params['HTTP_AUTHORIZATION'] ?? null;
                $response->getBody()->write("Hello, $name $authorization $basePath");
                return $response;
            })->add(PermissionMiddleware::class);;

            $app->get('/api/users', ListUsers::class);
            $app->post('/api/users', CreateUser::class);

            return $app;
        });
    }
}
