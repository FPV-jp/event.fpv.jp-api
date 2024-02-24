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

use Imagick;

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
        $bucket = $requestData['bucket'];

        $token = $request->getAttribute('token');
        $fileKey = $token['email'] . '/' . bin2hex(random_bytes(8));

        $uploadedFiles = $request->getUploadedFiles();
        $uploadedFile = $uploadedFiles['file'];

        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {

            $tempFileName = $uploadedFile->getStream()->getMetadata('uri');
            $fileSize = $uploadedFile->getSize();

            $clientMediaType = $uploadedFile->getClientMediaType();
            if (strpos($clientMediaType, 'video/') === 0||strpos($clientMediaType, 'image/') === 0) {

                $thumbnailFile = $uploadedFiles['thumbnail'];
                $tempThumbnailFileName = $thumbnailFile->getStream()->getMetadata('uri');
                $this->uploadWasabi($tempThumbnailFileName, $bucket, $fileKey . '_thumbnail');

            }

            if ($fileSize < 100 * 1024 * 1024) {// 100MB
                $this->uploadWasabi($tempFileName, $bucket, $fileKey);
            } else {
                $this->uploadWasabiMultipart($tempFileName, $bucket, $fileKey);
            }

        }

        $body = Stream::create(json_encode(['wasabi_file_key' => $fileKey], JSON_PRETTY_PRINT) . PHP_EOL);
        return new Response(201, ['Content-Type' => 'application/json'], $body);
    }


    private function uploadWasabi(string $tempFileName, string $bucket, string $fileKey)
    {
        try {
            $args = [
                'Bucket' => $bucket,
                'Key' => $fileKey,
                'Body' => file_get_contents($tempFileName),
            ];
            $this->wasabi->putObject($args);
        } catch (S3Exception $e) {
            // error_log('S3 Upload Error: ' . $e->getMessage());
        }
    }

    private function uploadWasabiMultipart(string $tempFileName, string $bucket, string $fileKey)
    {
        $uploader = new MultipartUploader($this->wasabi, $tempFileName, [
            'bucket' => $bucket,
            'key' => $fileKey,
        ]);

        $result = $uploader->upload();

        do {
            try {
                $result = $uploader->upload();
            } catch (MultipartUploadException $e) {
                $uploader = new MultipartUploader($this->wasabi, $tempFileName, [
                    'state' => $e->getState(),
                ]);
            }
        } while (!isset($result));
    }

}
