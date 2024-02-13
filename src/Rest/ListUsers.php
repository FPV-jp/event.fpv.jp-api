<?php declare(strict_types=1);

namespace FpvJp\Rest;

use Doctrine\ORM\EntityManager;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\Stream;
use Cloudinary\Api\Admin\AdminApi;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use FpvJp\Domain\User;
use function json_encode;

final class ListUsers implements RequestHandlerInterface
{
    private EntityManager $em;
    private AdminApi $api;

    public function __construct(EntityManager $em, AdminApi $api)
    {
        $this->em = $em;
        $this->api = $api;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var User[] $users */
        $users = $this->em->getRepository(User::class)->findAll();

        // $assets = $this->api->assets()->getArrayCopy();
        // error_log(print_r($assets['next_cursor'], true));
        // error_log(print_r($assets['resources'], true));

        $body = Stream::create(json_encode($users, JSON_PRETTY_PRINT) . PHP_EOL);

        return new Response(200, ['Content-Type' => 'application/json'], $body);
    }
}
