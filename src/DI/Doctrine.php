<?php declare(strict_types=1);

namespace UMA\FpvJpApi\DI;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Psr\Container\ContainerInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use UMA\DIC\Container;
use UMA\DIC\ServiceProvider;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Logging\SQLLogger;

/**
 * A ServiceProvider for registering services related to Doctrine in a DI container.
 * If the project had custom repositories (e.g. UserRepository) they could be registered here.
 * 
 * Doctrine関連のサービスをDIコンテナに登録するためのServiceProviderです。
 * プロジェクトにカスタムリポジトリ(UserRepository など)がある場合はここに登録できます。
 */
final class Doctrine implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function provide(Container $c): void
    {
        $c->set(EntityManager::class, static function (ContainerInterface $c): EntityManager {

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
            if ($settings['doctrine']['dev_mode']){
                $config->setSQLLogger(new EchoSQLLogger());
            }
            $connection = DriverManager::getConnection($settings['doctrine']['connection'], $config);
            return new EntityManager($connection, $config);
        });
    }
}

class EchoSQLLogger implements SQLLogger
{
    public function startQuery($sql, array $params = null, array $types = null)
    {
        error_log($sql);

        if ($params) {
            error_log("Parameters: " . print_r($params, true));
        }

        if ($types) {
            error_log("Types: " . print_r($types, true));
        }

    }

    public function stopQuery()
    {

    }
}