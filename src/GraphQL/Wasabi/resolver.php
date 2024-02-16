<?php declare(strict_types=1);

use Aws\Exception\AwsException;
use Aws\Api\DateTimeResult;
use Aws\S3\PostObjectV4;
use Aws\S3\Exception\S3Exception;

return [
    'listObjectsV2' => function ($rootValue, $args, $context) {
        try {
            $result = $this->wasabi->listObjectsV2([
                'Bucket' => 'fpv-japan',
            ]);
            // error_log(print_r([
            //     'Contents' => $result['Contents'],
            // ], true));
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
    'createPresignedRequest' => function ($rootValue, $args, $context) {
        $token = $context['token'];
        $bucket = 'fpv-japan';
        $user = $token['name'];
        $presignedUrls = [];
        try {
            foreach ($args['fileNames'] as $fileName) {
                $cmd = $this->wasabi->getCommand($args['command'], [
                    'Bucket' => $bucket,
                    'Key' => $user . '/' . $fileName,
                    'ACL' => 'public-read',
                ]);
                $request = $this->wasabi->createPresignedRequest($cmd, $args['expires']);
                $presignedUrls[] = [
                    'fileName' => $fileName,
                    'presignedUrl' => (string) $request->getUri(),
                ];
            }
            return $presignedUrls;
        } catch (S3Exception $e) {
            error_log(print_r($e, true));
            return $presignedUrls;
        }
    },
    'postObjectV4' => function ($rootValue, $args, $context) {
        // error_log(print_r($args, true));
        $token = $context['token'];
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
                $postObjectArray[] = array_merge($postObject->getFormAttributes(), $postObject->getFormInputs());
            }
            // error_log(print_r([
            //     'Objects' => $postObjectArray,
            // ], true));
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