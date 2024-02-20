<?php declare(strict_types=1);

namespace FpvJp\Rest;

use Aws\Exception\MultipartUploadException;
use Aws\S3\MultipartUploader;
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

        $token = $request->getAttribute('token');
        $fileKey = $token['email'] . '/' . bin2hex(random_bytes(8));

        $requestData = $request->getParsedBody();

        $uploadedFiles = $request->getUploadedFiles();
        $uploadedFile = $uploadedFiles['file'];

        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            try {
                $result = $this->wasabi->putObject([
                    'Bucket' => $requestData['bucket'],
                    'Key' => $fileKey,
                    'Body' => $uploadedFile->getStream(),
                ]);
                error_log(print_r($result->toArray(), true));
            } catch (S3Exception $e) {
                error_log(print_r($e, true));
            }

            // $source = fopen('/path/to/large/file.zip', 'rb');
            // $uploader = new MultipartUploader($this->wasabi, $source, [
            //     'bucket' => $requestData['bucket'],
            //     'key' => $requestData['user_email'] . '/' . bin2hex(random_bytes(8)),
            // ]);
            // do {
            //     try {
            //         $result = $uploader->upload();
            //     } catch (MultipartUploadException $e) {
            //         rewind($source);
            //         $uploader = new MultipartUploader($this->wasabi, $source, [
            //             'state' => $e->getState(),
            //         ]);
            //     }
            // } while (!isset($result));
            // fclose($source);
        }

        $body = Stream::create(json_encode(['fileKey' => $fileKey], JSON_PRETTY_PRINT) . PHP_EOL);

        return new Response(201, ['Content-Type' => 'application/json'], $body);
    }
}
