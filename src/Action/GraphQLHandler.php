<?php declare(strict_types=1);

namespace UMA\FpvJpApi\GraphQLHandler;

use Doctrine\ORM\EntityManager;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\Stream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use UMA\FpvJpApi\Domain\User;
use function json_encode;

final class GraphQLHandler implements RequestHandlerInterface
{
    private EntityManager $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var User[] $users */
        $users = $this->em
            ->getRepository(User::class)
            ->findAll();

        $body = Stream::create(json_encode($users, JSON_PRETTY_PRINT) . PHP_EOL);

        return new Response(200, ['Content-Type' => 'application/json'], $body);
    }
}
