<?php declare(strict_types=1);

namespace UMA\FpvJpApi\DI;

use Doctrine\ORM\EntityManager;
use Faker\Factory;
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

use Psr\Log\LoggerInterface;
use UMA\FpvJpApi\DI\MonologLogger;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

use UMA\FpvJpApi\DI\PermissionMiddleware;
use UMA\FpvJpApi\Action\CreateUser;
use UMA\FpvJpApi\Action\ListUsers;
use UMA\FpvJpApi\Action\GraphQLHandler;

use PHPMailer\PHPMailer\PHPMailer;

use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Admin\AdminApi;



/**
 * A ServiceProvider for registering services related to Slim such as request handlers,
 * routing and the App service itself that wires everything together.
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
            return new CreateUser($c->get(EntityManager::class), $c->get(PHPMailer::class), Factory::create());
        });

        $c->set(GraphQLHandler::class, static function (ContainerInterface $c): RequestHandlerInterface {
            return new GraphQLHandler($c->get(EntityManager::class), Factory::create());
        });

        $c->set(App::class, static function (ContainerInterface $ci): App {

            /** @var array $settings */
            $settings = $ci->get('settings');

            $app = AppFactory::create(null, $ci);

            $app->addRoutingMiddleware();
            // $container = $app->getContainer();
            // $container['schema'] = function ($container) {
            //     $schemaString = file_get_contents(__DIR__ . '/schema.graphql');
            //     return BuildSchema::build($schemaString);
            // };

            // $app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function ($request, $response) {
            //     throw new HttpNotFoundException($request);
            // });
            $app->add(function ($request, $handler) {
                $response = $handler->handle($request);
                return $response
                    ->withHeader('Access-Control-Allow-Origin', '*')
                    ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
                    ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
            });

            $app->add(new ContentLengthMiddleware());

            // $app->add(function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
            //     return $handler->handle($request);
            // });

            // $app->get('/api/hello/{name}', function (ServerRequestInterface $request, ResponseInterface $response, $args) {
            //     $routeContext = RouteContext::fromRequest($request);
            //     $basePath = $routeContext->getBasePath();
            //     $name = $args['name'];
            //     $params = $request->getServerParams();
            //     $authorization = $params['HTTP_AUTHORIZATION'] ?? null;
            //     $response->getBody()->write("Hello, $name $authorization $basePath");
            //     return $response;
            // })->add(PermissionMiddleware::class);

            $app->get('/api/cloudinary', function ($request, $response, $args) {
                $config = new Configuration();
                $config->cloud->cloudName = '';
                $config->cloud->apiKey = '';
                $config->cloud->apiSecret = '';
                $config->url->secure = true;
                $admin = new AdminApi($config);
                $responseData = $admin->assets();
                $jsonResponse = json_encode($responseData);
                $response->getBody()->write($jsonResponse);
                return $response->withHeader('Content-Type', 'application/json');
            });

            $app->options('/{routes:.+}', function ($request, $response, $args) {
                return $response;
            });

            $app->get('/api/users', ListUsers::class);
            $app->post('/api/users', CreateUser::class);

            $app->post('/graphql', GraphQLHandler::class);

            $logger = new MonologLogger('app', [new StreamHandler(__DIR__ . '/app.log', Logger::DEBUG)]);
            $app->addErrorMiddleware(
                $settings['slim']['displayErrorDetails'],
                $settings['slim']['logErrors'],
                $settings['slim']['logErrorDetails'],
                $logger
            );

            return $app;
        });
    }
}
