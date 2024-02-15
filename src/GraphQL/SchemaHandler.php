<?php declare(strict_types=1);

namespace FpvJp\GraphQL;

use Doctrine\ORM\EntityManager;

use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Aws\Api\DateTimeResult;
use Aws\S3\PostObjectV4;
use Aws\S3\Exception\S3Exception;

use Cloudinary\Api\Admin\AdminApi;
use PHPMailer\PHPMailer\PHPMailer;
use Faker\Generator;

use FpvJp\Domain\User;

use Nyholm\Psr7\Response;
use Nyholm\Psr7\Stream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use GraphQL\GraphQL;
use GraphQL\Utils\BuildSchema;
use GraphQL\Error\FormattedError;

use Slim\Exception\HttpInternalServerErrorException;

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

    private function getS3Resolver(): array
    {
        return [
            'listObjectsV2' => function ($rootValue, $args, $context) {
                try {
                    $result = $this->wasabi->listObjectsV2([
                        'Bucket' => 'fpv-japan',
                    ]);
                    foreach ($result['Contents'] as $Content) {
                        $Content['LastModified'] = $Content['LastModified']->jsonSerialize();
                    }
                    return [
                        'Contents' => $result['Contents'],
                    ];
                } catch (S3Exception $e) {
                    error_log(print_r($e, true));
                    return [
                        'Contents' => [],
                    ];
                }
            },
            'postObjectV4' => function ($rootValue, $args, $context) {
                error_log(print_r($args, true));
                $token = $context['token'];
                error_log(print_r($token, true));
                $bucket = 'fpv-japan';
                $starts_with = $token['name'];
                $this->wasabi->listBuckets();
                $postObjectArray = [];
                try {
                    foreach ($args['names'] as $name) {
                        $formInputs = [
                            'acl' => 'public-read',
                            'key' => $starts_with . '/' . $name
                        ];
                        $options = [
                            ['acl' => 'public-read'],
                            ['bucket' => $bucket],
                            ['starts-with', '$key', $starts_with],
                        ];
                        // $expires = '+2 hours';
                        $expires = '+5 minutes';
                        $postObject = new PostObjectV4(
                            $this->wasabi,
                            $bucket,
                            $formInputs,
                            $options,
                            $expires
                        );
                        $postObjectArray[] = [
                            'formAttributes' => $postObject->getFormAttributes(),
                            'formInputs' => $postObject->getFormInputs()
                        ];
                    }
    
                    error_log(print_r([
                        'Objects' => $postObjectArray,
                    ], true));

                    return [
                        'Objects' => $postObjectArray,
                    ];
                } catch (S3Exception $e) {
                    error_log(print_r($e, true));
                    return [
                        'Objects' => [],
                    ];
                }
            },
        ];
    }

    private function getCloudinaryResolver(): array
    {
        return [
            'assets' => function ($rootValue, $args, $context) {
                $assets = $this->cloudinary->assets()->getArrayCopy();
                // error_log(print_r($assets['next_cursor'], true));
                // error_log(print_r($assets['resources'], true));
                return $assets;
            },
        ];
    }

    private function getUserResolver(): array
    {
        return [
            'user' => function ($rootValue, $args, $context) {
                $user = $this->em->getRepository(User::class)->find($args['id']);
                return $user->jsonSerialize();
            },
            'allUsers' => function ($rootValue, $args, $context) {
                $token = $context['token'];
                error_log(print_r($token, true));

                $users = $this->em->getRepository(User::class)->findAll();
                $userArray = [];
                foreach ($users as $user) {
                    $userArray[] = $user->jsonSerialize();
                }
                return $userArray;
            },
            'createUser' => function ($rootValue, $args, $context) {
                // $newUser = new User($args['email'], $args['password']);
                $newRandomUser = new User($this->faker->email(), $this->faker->password());
                $this->em->persist($newRandomUser);
                $this->em->flush();
                return $newRandomUser->jsonSerialize();
            },
            'updateUser' => function ($rootValue, $args, $context) {
                $user = $this->em->getRepository(User::class)->find($args['id']);
                $user->updateParameters($args);
                $this->em->flush();
                return $user->jsonSerialize();
            },
            'deleteUser' => function ($rootValue, $args, $context) {
                $user = $this->em->getRepository(User::class)->find($args['id']);
                $this->em->remove($user);
                $this->em->flush();
                return $user->jsonSerialize();
            }
        ];
    }
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $rawInput = file_get_contents('php://input');
        if ($rawInput === false) {
            throw new HttpInternalServerErrorException($request, 'Failed to get php://input');
        }
        $input = json_decode($rawInput, true);
        try {
            $schema = BuildSchema::build(file_get_contents(__DIR__ . '/schema.graphql'));
            $source = $input['query'];
            $rootValue = [];
            $rootValue = array_merge($rootValue, $this->getUserResolver());
            $rootValue = array_merge($rootValue, $this->getCloudinaryResolver());
            $rootValue = array_merge($rootValue, $this->getS3Resolver());
            $contextValue = ['token' => $request->getAttribute('token')];
            $variableValues = $input['variables'] ?? null;
            $result = GraphQL::executeQuery(
                $schema,
                $source,
                $rootValue,
                $contextValue,
                $variableValues,
                // string $operationName = null,
                // callable $fieldResolver = null,
                // array $validationRules = null
            );
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
