<?php declare(strict_types=1);

namespace FpvJp\GraphQL;

use Faker\Generator;
use Doctrine\ORM\EntityManager;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\Stream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use FpvJp\GraphQL\Resolver\CloudinaryResolver;
use FpvJp\GraphQL\Resolver\S3Resolver;
use FpvJp\GraphQL\Resolver\UserResolver;

use GraphQL\GraphQL;
use GraphQL\Utils\BuildSchema;
use GraphQL\Error\FormattedError;

use Slim\Exception\HttpInternalServerErrorException;

use Aws\S3\S3Client;
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
    private S3Client $wasabi;

    private Generator $faker;

    public function __construct(EntityManager $em, AdminApi $cloudinary, PHPMailer $mailer, S3Client $wasabi, Generator $faker)
    {
        $this->em = $em;
        $this->cloudinary = $cloudinary;
        $this->mailer = $mailer;
        $this->wasabi = $wasabi;
        $this->faker = $faker;
    }

    private function getRoot(): array {
        $userResolver = include __DIR__ . '/Resolver/UserResolver.php';
        $cloudinaryResolver = include __DIR__ . '/Resolver/CloudinaryResolver.php';

        $rootValue = [];
        $rootValue = array_merge($rootValue, $userResolver);
        $rootValue = array_merge($rootValue, $cloudinaryResolver);

        // $userResolver = new UserResolver($this->em, $this->faker);
        // $cloudinaryResolver = new CloudinaryResolver($this->cloudinary, $this->wasabi);
        // $s3ResolverResolver = new S3Resolver($this->cloudinary, $this->wasabi);

        // return [
        //     'user' => [$userResolver, 'user'],
        //     'allUsers' => [$userResolver, 'allUsers'],
        //     'createUser' => [$userResolver, 'createUser'],
        //     'updateUser' => [$userResolver, 'updateUser'],
        //     'deleteUser' => [$userResolver, 'deleteUser'],

        //     'assets' => [$cloudinaryResolver, 'assets'],

        //     'echo' => [$s3ResolverResolver, 'echo'],
        // ];
        // error_log(print_r($rootValue['echo'], true));
        return $rootValue;
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
            $rootValue = $this->getRoot();
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
