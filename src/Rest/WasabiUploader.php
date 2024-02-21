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

    private function saveThumbnail(Imagick $image, string $bucket, string $fileKey, string $thumbnail)
    {

        if ($thumbnail == 'map') {
            $image->resizeImage(200, 200, Imagick::FILTER_LANCZOS, 1);

            $tempFileName = tempnam(sys_get_temp_dir(), 'resizedImage');
            file_put_contents($tempFileName, $image->getImageBlob());
            $imageInfo = getimagesize($tempFileName);

            $newSize = min($imageInfo[0], $imageInfo[1]);
            $x = max(0, ($imageInfo[0] - $newSize) / 2);
            $y = max(0, ($imageInfo[1] - $newSize) / 2);
            $image->cropImage($newSize, $newSize, $x, $y);
        }

        if ($thumbnail == 'media') {
            $image->resizeImage(343, 343, Imagick::FILTER_LANCZOS, 1);

            // $tempFileName = tempnam(sys_get_temp_dir(), 'resizedImage');
            // file_put_contents($tempFileName, $image->getImageBlob());
            // $imageInfo = getimagesize($tempFileName);

            // $newSize = min($imageInfo[0], $imageInfo[1]);
            // //     $newWidth = (int)round($imageInfo[1] * 1.618); 
            // if ($imageInfo[0] > $imageInfo[1]) {

            // }
        }

        try {
            $this->wasabi->putObject([
                'Bucket' => $bucket,
                'Key' => $fileKey . '_thumbnail',
                'Body' => $image->getImageBlob(),
            ]);
        } catch (S3Exception $e) {
            error_log('S3 Upload Error: ' . $e->getMessage());
        } finally {
            if ($thumbnail == 'map') {
                unlink($tempFileName);
            }
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
        $thumbnail = $requestData['thumbnail'];

        error_log(print_r($requestData, true));

        $token = $request->getAttribute('token');
        $fileKey = $token['email'] . '/' . bin2hex(random_bytes(8));

        // $tempFileName = $this->saveFileTempDir($request);
        $uploadedFiles = $request->getUploadedFiles();
        error_log(print_r($uploadedFiles, true));

        $uploadedFile = $uploadedFiles['file'];

        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {

            $tempFileName = $uploadedFile->getStream()->getMetadata('uri');
            $image = new Imagick($tempFileName);
            $this->saveThumbnail($image, $bucket, $fileKey, $thumbnail);
            $this->saveImage($tempFileName, $bucket, $fileKey);

        }

        $body = Stream::create(json_encode(['fileKey' => $fileKey], JSON_PRETTY_PRINT) . PHP_EOL);
        return new Response(201, ['Content-Type' => 'application/json'], $body);
    }
}
