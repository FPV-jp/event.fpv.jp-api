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
            putenv('AWS_CONFIG_FILE=' . $settings['wasabi']['config']);
            $s3Client = S3Client::factory($raw_credentials);
            return $s3Client;
        });
    }
}
