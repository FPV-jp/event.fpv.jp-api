<?php declare(strict_types=1);

use Aws\Exception\AwsException;
use Aws\S3\Exception\S3Exception;

return [
    'assets' => function ($rootValue, $args, $context) {
        // $assets = $this->cloudinary->assets()->getArrayCopy();
        // error_log(print_r($assets['next_cursor'], true));
        // error_log(print_r($assets['resources'], true));
        return $this->cloudinary->assets()->getArrayCopy();
    },
    'echo' => function ($rootValue, $args, $context) {
        try {
            // $result = $this->wasabi->putObject([
            //     'Bucket' => 'fpv-japan',
            //     'Key' => 'my-key',
            //     'Body' => 'this is the body!'
            // ]);
            $result = $this->wasabi->listObjectsV2([
                'Bucket' => 'fpv-japan',
            ]);
            
            // $result = $this->wasab->listObjects([
            //     'Bucket' => 'fpv-japan'
            // ]);
            error_log(print_r($result, true));
        } catch (S3Exception $e) {
            return [
                'message' => $e->getMessage(),
            ];
            // error_log(print_r($e, true));
        }
        return [
            'message' => 'xxxxx',
        ];
    },
];

// try {
//     $s3Client->createBucket(['Bucket' => 'my-bucket']);
// } catch (S3Exception $e) {
//     // Catch an S3 specific exception.
//     echo $e->getMessage();
// } catch (AwsException $e) {
//     // This catches the more generic AwsException. You can grab information
//     // from the exception using methods of the exception object.
//     echo $e->getAwsRequestId() . "\n";
//     echo $e->getAwsErrorType() . "\n";
//     echo $e->getAwsErrorCode() . "\n";

//     // This dumps any modeled response data, if supported by the service
//     // Specific members can be accessed directly (e.g. $e['MemberName'])
//     var_dump($e->toArray());
// }