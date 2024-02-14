<?php declare(strict_types=1);

namespace Tests\FpvJp\Unit;

use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Slim\App;
use UMA\DIC\Container;
use FpvJp\DI;

final class ProvidersTest extends TestCase
{
    public function testContainer(): void
    {
        $sut = new Container($GLOBALS['testingSettings']);

        $sut->register(new DI\Slim());
        $sut->register(new DI\Doctrine());
        $sut->register(new DI\CloudinaryAdmin());
        $sut->register(new DI\Wasabi());
        $sut->register(new DI\Mailer());

        self::assertInstanceOf(App::class, $sut->get(App::class));
        self::assertInstanceOf(EntityManager::class, $sut->get(EntityManager::class));
    }
}
