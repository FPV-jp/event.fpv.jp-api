<?php declare(strict_types=1);

namespace FpvJp\Action;

use Faker\Generator;
use Doctrine\ORM\EntityManager;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\Stream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use FpvJp\Domain\User;

use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use GraphQL\Error\FormattedError;

use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpUnauthorizedException;
use Slim\Exception\HttpInternalServerErrorException;

use DateTimeImmutable;

use function json_encode;

final class GraphQLHandler implements RequestHandlerInterface
{
    private EntityManager $em;
    private Generator $faker;

    public function __construct(EntityManager $em, Generator $faker)
    {
        $this->em = $em;
        $this->faker = $faker;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $rawInput = file_get_contents('php://input');
        if ($rawInput === false) {
            throw new HttpInternalServerErrorException($request, 'Failed to get php://input');
        }
        $input = json_decode($rawInput, true);

        try {
            $schemaString = file_get_contents(__DIR__ . '/schema.graphql');
            $schema = BuildSchema::build($schemaString);

            $query = $input['query'];

            $rootValue = [
                'user' => function ($rootValue, $args) use ($request) {
                    $user = $this->em->getRepository(User::class)->find($args['id']);
                    if (!$user) {
                        throw new HttpNotFoundException($request, 'User not found');
                    }
                    return $user->jsonSerialize();
                },
                'allUsers' => function () {
                    $users = $this->em->getRepository(User::class)->findAll();
                    $userArray = [];
                    foreach ($users as $user) {
                        $userArray[] = $user->jsonSerialize();
                    }
                    return $userArray;
                },
                'createUser' => function ($rootValue, $args) {
                    // $newUser = new User($args['email'], $args['password']);
                    $newRandomUser = new User($this->faker->email(), $this->faker->password());
                    $this->em->persist($newRandomUser);
                    $this->em->flush();
                    return $newRandomUser->jsonSerialize();
                },
                'updateUser' => function ($rootValue, $args) use ($request) {
                    $user = $this->em->getRepository(User::class)->find($args['id']);
                    if (!$user) {
                        throw new HttpNotFoundException($request, 'User not found');
                    }
                    $user->updateParameters($args);
                    $this->em->flush();
                    return $user->jsonSerialize();
                },
                'deleteUser' => function ($rootValue, $args) use ($request) {
                    $user = $this->em->getRepository(User::class)->find($args['id']);
                    if (!$user) {
                        throw new HttpNotFoundException($request, 'User not found');
                    }
                    $this->em->remove($user);
                    $this->em->flush();
                    return $user->jsonSerialize();
                }
            ];

            $contextValue = [
                'user' => [
                    'id' => 123,
                    'name' => 'John Doe'
                ]
            ];

            $variableValues = $input['variables'] ?? null;

            $result = GraphQL::executeQuery($schema, $query, $rootValue, $contextValue, $variableValues);

        } catch (\Exception $e) {
            error_log($e->getMessage());
            $result = [
                'errors' => [
                    FormattedError::createFromException($e)
                ]
            ];
        }

        $body = Stream::create(json_encode($result, JSON_PRETTY_PRINT) . PHP_EOL);
        return new Response(200, ['Content-Type' => 'application/json'], $body);
    }
}
