<?php declare(strict_types=1);

namespace FpvJp\GraphQL;

use Faker\Generator;
use Doctrine\ORM\EntityManager;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\Stream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use GraphQL\GraphQL;
use GraphQL\Utils\BuildSchema;
use GraphQL\Error\FormattedError;

use Slim\Exception\HttpInternalServerErrorException;

use Cloudinary\Api\Admin\AdminApi;
use PHPMailer\PHPMailer\{
    Exception,
    PHPMailer
};
use function json_encode;

final class SchemaHandler implements RequestHandlerInterface
{
    private EntityManager $em;
    private AdminApi $cloudinary;
    private PHPMailer $mailer;
    private Generator $faker;

    public function __construct(EntityManager $em, AdminApi $cloudinary, PHPMailer $mailer, Generator $faker)
    {
        $this->em = $em;
        $this->cloudinary = $cloudinary;
        $this->mailer = $mailer;
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
            $userResolver = include 'UserResolver.php';
            $cloudinaryResolver = include 'CloudinaryResolver.php';

            $rootValue = [];
            $rootValue = array_merge($rootValue, $userResolver);
            $rootValue = array_merge($rootValue, $cloudinaryResolver);

            $contextValue = ['token' => $request->getAttribute('token')];
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
