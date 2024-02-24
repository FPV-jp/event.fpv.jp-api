<?php declare(strict_types=1);

namespace FpvJp\Rest;

use Aws\Exception\MultipartUploadException;
use Aws\S3\MultipartUploader;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;

use Nyholm\Psr7\Response;
use Nyholm\Psr7\Stream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

use Psr\Http\Server\RequestHandlerInterface;

final class WasabiDownloader implements RequestHandlerInterface
{
    private S3Client $wasabi;

    public function __construct(S3Client $wasabi)
    {
        $this->wasabi = $wasabi;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {

        $requestData = $request->getParsedBody();

        try {
            $result = $this->wasabi->getObject([
                'Bucket' => $requestData['bucket'],
                'Key' => $requestData['fileKey'],
            ]);
            // error_log(print_r($result, true));
        } catch (S3Exception $e) {
            return new Response(500, ['Content-Type' => 'text/plain'], 'Failed to retrieve file from Wasabi');
        }

        $stream = Stream::create($result['Body']->getContents());
        return new Response(200, ['Content-Type' => $result['ContentType']], $stream);

        // $body = Stream::create(json_encode(['fileKey' => ''], JSON_PRETTY_PRINT) . PHP_EOL);
        // return new Response(200, ['Content-Type' => 'application/json'], $body);
    }
}
