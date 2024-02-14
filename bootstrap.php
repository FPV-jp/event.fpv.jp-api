<?php declare(strict_types=1);

// bootstrap.php

use Doctrine\Common\Cache\Psr6\DoctrineProvider;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\ORMSetup;
use Doctrine\DBAL\DriverManager;
use Psr\Container\ContainerInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use UMA\DIC\Container;
use FpvJp\DI\EchoSQLLogger;
// use Cloudinary\Configuration\Configuration;

require_once __DIR__ . '/vendor/autoload.php';

$container = new Container(require __DIR__ . '/settings.php');

$container->set(EntityManager::class, static function (Container $c): EntityManager {
    /** @var array $settings */
    $settings = $c->get('settings');

    $config = ORMSetup::createAttributeMetadataConfiguration(
        $settings['doctrine']['metadata_dirs'],
        $settings['doctrine']['dev_mode'],
        null,
        $settings['doctrine']['dev_mode'] ?
        new ArrayAdapter() :
        new FilesystemAdapter(directory: $settings['doctrine']['cache_dir']),
        true
    );
    if ($settings['doctrine']['dev_mode']) {
        $config->setSQLLogger(new EchoSQLLogger());
    }
    $connection = DriverManager::getConnection($settings['doctrine']['connection'], $config);
    return new EntityManager($connection, $config);
});

return $container;
