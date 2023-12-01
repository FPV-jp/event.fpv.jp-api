<?php

declare(strict_types=1);

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

use UMA\FpvJpApi\DI\PermissionMiddleware;
use UMA\FpvJpApi\Action\CreateUser;
use UMA\FpvJpApi\Action\ListUsers;

use UMA\FpvJpApi\Action\Dashboard;

use UMA\FpvJpApi\Action\Apps\Calendar;
use UMA\FpvJpApi\Action\Apps\Gallery;

use UMA\FpvJpApi\Action\Apps\Taskboard\KanbanBoard;
use UMA\FpvJpApi\Action\Apps\Taskboard\Pipeline;
use UMA\FpvJpApi\Action\Apps\Taskboard\ProjectsBoard;

use UMA\FpvJpApi\Action\Pages\Profile;
use UMA\FpvJpApi\Action\Pages\EditProfile;
use UMA\FpvJpApi\Action\Pages\Account;
use PHPMailer\PHPMailer\PHPMailer;

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
            return new CreateUser($c->get(EntityManager::class), Factory::create());
        });
        
        //Dashboard
        $c->set(Dashboard::class, static function (ContainerInterface $c): RequestHandlerInterface {
            return new Dashboard($c->get(EntityManager::class), Factory::create());
        });

        //Apps
        $c->set(Calendar::class, static function (ContainerInterface $c): RequestHandlerInterface {
            return new Calendar($c->get(EntityManager::class), Factory::create(), new PHPMailer(true));
        });

        $c->set(Gallery::class, static function (ContainerInterface $c): RequestHandlerInterface {
            return new Gallery($c->get(EntityManager::class), Factory::create());
        });

        //Pages
        $c->set(Profile::class, static function (ContainerInterface $c): RequestHandlerInterface {
            return new Profile($c->get(EntityManager::class), Factory::create());
        });

        $c->set(EditProfile::class, static function (ContainerInterface $c): RequestHandlerInterface {
            return new EditProfile($c->get(EntityManager::class), Factory::create());
        });

        $c->set(Account::class, static function (ContainerInterface $c): RequestHandlerInterface {
            return new Account($c->get(EntityManager::class), Factory::create());
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

            // $app->get('/api/hello/{name}', function (ServerRequestInterface $request, ResponseInterface $response, $args) {
            //     $routeContext = RouteContext::fromRequest($request);
            //     $basePath = $routeContext->getBasePath();
            //     $name = $args['name'];
            //     $params = $request->getServerParams();
            //     $authorization = $params['HTTP_AUTHORIZATION'] ?? null;
            //     $response->getBody()->write("Hello, $name $authorization $basePath");
            //     return $response;
            // })->add(PermissionMiddleware::class);

            $app->get('/api/users', ListUsers::class);
            $app->post('/api/users', CreateUser::class);

            //Dashboard
            $app->get('/api/dashboard', Dashboard::class);

            //Apps
            $app->get('/api/apps/calendar', Calendar::class)->add(PermissionMiddleware::class);
            $app->get('/api/apps/gallery', Gallery::class)->add(PermissionMiddleware::class);

            $app->get('/api/apps/taskboard/projects-board', KanbanBoard::class);
            $app->get('/api/apps/taskboard/kanban-board', Pipeline::class);
            $app->get('/api/apps/taskboard/pipeline', ProjectsBoard::class);

            //Pages
            $app->get('/api/pages/profile', Profile::class);
            $app->get('/api/pages/edit-profile', EditProfile::class);
            $app->get('/api/pages/account', Account::class);

            return $app;
        });
    }
}

// export const privateRoutes = [
//     { path: 'dashboard', exact: 'true', component: Dashboard },

//     //Apps
//     { path: 'apps/chat/chats', exact: 'true', component: Chats },
//     { path: 'apps/chat/chat-groups', exact: 'true', component: ChatGroups },
//     { path: 'apps/chat/chat-contact', exact: 'true', component: ChatContacts },

//     { path: 'apps/chat-bot/chatpopup', exact: 'true', component: ChatPopup },
//     { path: 'apps/chat-bot/chatbot', exact: 'true', component: ChatBot },

//     { path: 'apps/calendar', exact: 'true', component: Calendar },

//     { path: 'apps/email', exact: 'true', component: Email },

//     { path: 'apps/taskboard/projects-board', exact: 'true', component: ProjectsBoard },
//     { path: 'apps/taskboard/kanban-board', exact: 'true', component: KanbanBoard },
//     { path: 'apps/taskboard/pipeline', exact: 'true', component: Pipeline },

//     { path: 'apps/contacts/contact-list', exact: 'true', component: ContactList },
//     { path: 'apps/contacts/contact-cards', exact: 'true', component: ContactCards },
//     { path: 'apps/contacts/edit-contact', exact: 'true', component: EditContact },

//     { path: 'apps/file-manager/list-view', exact: 'true', component: ListView },
//     { path: 'apps/file-manager/grid-view', exact: 'true', component: GridView },

//     { path: 'apps/gallery', exact: 'true', component: Gallery },

//     { path: 'apps/todo/task-list', exact: 'true', component: TaskList },
//     { path: 'apps/todo/gantt', exact: 'true', component: Gantt },

//     { path: 'apps/blog/posts', exact: 'true', component: Posts },
//     { path: 'apps/blog/add-new-post', exact: 'true', component: AddNewPost },
//     { path: 'apps/blog/post-detail', exact: 'true', component: PostDetail },

//     { path: 'apps/invoices/invoice-list', exact: 'true', component: InvoiceList },
//     { path: 'apps/invoices/invoice-templates', exact: 'true', component: InvoiceTemplates },
//     { path: 'apps/invoices/create-invoice', exact: 'true', component: CreateInvoice },
//     { path: 'apps/invoices/invoice-preview', exact: 'true', component: PreviewInvoice },

//     { path: 'apps/integrations/all-apps', exact: 'true', component: AllApps },

//     { path: 'apps/integrations/integrations-detail', exact: 'true', component: IntegrationsDetail },
//     { path: 'apps/integrations/integration', exact: 'true', component: Integration },

//     //Pages
//     { path: 'pages/profile', exact: 'true', component: Profile },
//     { path: 'pages/edit-profile', exact: 'true', component: EditProfile },
//     { path: 'pages/account', exact: 'true', component: Account },

//     //Error
//     { path: 'error-404', exact: 'true', component: Error404 },
//   ]