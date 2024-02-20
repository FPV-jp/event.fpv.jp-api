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

    private function saveFileTempDir(ServerRequestInterface $request): string|null
    {
        $uploadedFiles = $request->getUploadedFiles();
        $uploadedFile = $uploadedFiles['file'];
        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            $filename = $uploadedFile->getClientFilename();
            $stream = $uploadedFile->getStream();
            $tempFileName = tempnam(sys_get_temp_dir(), $filename);
            if (is_resource($stream)) {
                $fileData = stream_get_contents($stream);
                file_put_contents($tempFileName, $fileData);
            } else {
                file_put_contents($tempFileName, $stream);
            }
            return $tempFileName;
        }
        return null;
    }

    private function saveThumbnail(string $tempFileName, string $bucket, string $fileKey)
    {
        $image = new Imagick($tempFileName);
        $newWidth = 100;
        $newHeight = 100;
        $image->resizeImage($newWidth, $newHeight, Imagick::FILTER_LANCZOS, 1);

        try {
            $this->wasabi->putObject([
                'Bucket' => $bucket,
                'Key' => $fileKey . '_thumbnail',
                'Body' => $image->getImageBlob(),
            ]);
        } catch (S3Exception $e) {
            error_log('S3 Upload Error: ' . $e->getMessage());
        }
    }


    private function saveImage(string $tempFileName, string $bucket, string $fileKey)
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

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $requestData = $request->getParsedBody();
        $bucket = $requestData['bucket'];

        $token = $request->getAttribute('token');
        $fileKey = $token['email'] . '/' . bin2hex(random_bytes(8));

        $tempFileName = $this->saveFileTempDir($request);

        if ($tempFileName) {
            try {
                $this->saveThumbnail($tempFileName, $bucket, $fileKey);
                $this->saveImage($tempFileName, $bucket, $fileKey);
            } finally {
                unlink($tempFileName);
            }
        }

        $body = Stream::create(json_encode(['fileKey' => $fileKey], JSON_PRETTY_PRINT) . PHP_EOL);
        return new Response(201, ['Content-Type' => 'application/json'], $body);
    }
}
