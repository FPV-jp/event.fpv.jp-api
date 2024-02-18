<?php declare(strict_types=1);

namespace FpvJp\Rest;

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;

use Nyholm\Psr7\Response;
use Nyholm\Psr7\Stream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use Psr\Http\Server\RequestHandlerInterface;

use function json_encode;

final class WasabiUploader implements RequestHandlerInterface
{
    private S3Client $wasabi;

    public function __construct(S3Client $wasabi)
    {
        $this->wasabi = $wasabi;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $requestData = $request->getParsedBody();

        $uploadedFiles = $request->getUploadedFiles();
        $uploadedFile = $uploadedFiles['file'];

        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {

            try {
                $result = $this->wasabi->putObject([
                    'Bucket' => $requestData['bucket'],
                    'Key' => $requestData['user_email'] . '/' . bin2hex(random_bytes(8)),
                    'Body' => $uploadedFile->getStream(),
                ]);
            } catch (S3Exception $e) {
                error_log(print_r($e->getMessage(), true));
            }

        }

        $body = Stream::create(json_encode($result->toArray(), JSON_PRETTY_PRINT) . PHP_EOL);

        return new Response(201, ['Content-Type' => 'application/json'], $body);
    }
}
