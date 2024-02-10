<?php declare(strict_types=1);

namespace FpvJp\DI;

use Psr\Container\ContainerInterface;
use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Admin\AdminApi;
use UMA\DIC\Container;
use UMA\DIC\ServiceProvider;

class CloudinaryAdmin implements ServiceProvider
{
    public function provide(Container $c): void
    {
        $c->set(AdminApi::class, static function (ContainerInterface $c): AdminApi {

            /** @var array $settings */
            $settings = $c->get('settings');

            $config = new Configuration();
            $config->cloud->cloudName = $settings['cloudinary']['cloudname'];
            $config->cloud->apiKey = $settings['cloudinary']['apikey'];
            $config->cloud->apiSecret = $settings['cloudinary']['apisecret'];
            $config->url->secure = true;

            return new AdminApi($config);

        });
    }
}
