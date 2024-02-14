<?php declare(strict_types=1);

namespace FpvJp\DI;

use Psr\Container\ContainerInterface;
use Aws\S3\S3Client;
use UMA\DIC\Container;
use UMA\DIC\ServiceProvider;

class Wasabi implements ServiceProvider
{
    public function provide(Container $c): void
    {
        $c->set(S3Client::class, static function (ContainerInterface $c): S3Client {

            /** @var array $settings */
            $settings = $c->get('settings');

            $raw_credentials = array(
                'credentials' => [
                    'key' => $settings['wasabi']['apikey'],
                    'secret' => $settings['wasabi']['apisecret']
                ],
                'endpoint' => 'https://s3.ap-northeast-1.wasabisys.com',
                'region' => 'ap-northeast-1',
                'version' => 'latest',
                'use_path_style_endpoint' => true
            );
            
            $s3Client = S3Client::factory($raw_credentials);
            return $s3Client;
        });
    }
}

// $source = '/path/to/large/file.zip';
// $uploader = new MultipartUploader($s3Client, $source, [
//     'bucket' => 'your-bucket',
//     'key' => 'my-file.zip',
// ]);

// try {
//     $result = $uploader->upload();
//     echo "Upload complete: {$result['ObjectURL']}\n";
// } catch (MultipartUploadException $e) {
//     echo $e->getMessage() . "\n";
// }

// $source = '/path/to/large/file.zip';
// $uploader = new MultipartUploader($s3Client, $source, [
//     'bucket' => 'your-bucket',
//     'key' => 'my-file.zip',
// ]);

// $promise = $uploader->promise();

// try {
//     $result = $s3Client->putObject([
//         'Bucket' => $bucket,
//         'Key' => $key,
//         'SourceFile' => $file_Path,
//     ]);
// } catch (S3Exception $e) {
//     echo $e->getMessage() . "\n";
// }

// // Send a PutObject request and get the result object.
// $result = $s3Client->putObject([
//     'Bucket' => 'my-bucket',
//     'Key' => 'my-key',
//     'Body' => 'this is the body!'
// ]);

// // Download the contents of the object.
// $result = $s3Client->getObject([
//     'Bucket' => 'my-bucket',
//     'Key' => 'my-key'
// ]);

// try {
//     $contents = $this->s3client->listObjectsV2([
//         'Bucket' => $this->bucketName,
//     ]);
//     echo "The contents of your bucket are: \n";
//     foreach ($contents['Contents'] as $content) {
//         echo $content['Key'] . "\n";
//     }
// } catch (Exception $exception) {
//     echo "Failed to list objects in $this->bucketName with error: " . $exception->getMessage();
//     exit("Please fix error with listing objects before continuing.");
// }

// try {
//     $objects = $s3->listObjects([
//         'Bucket' => $bucket
//     ]);
//     foreach ($objects['Contents'] as $object) {
//         echo $object['Key'] . PHP_EOL;
//     }
// } catch (S3Exception $e) {
//     echo $e->getMessage() . PHP_EOL;
// }
