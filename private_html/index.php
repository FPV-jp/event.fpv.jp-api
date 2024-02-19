<?php declare(strict_types=1);

ini_set('display_errors', "On");

ini_set('upload_max_filesize', '1G');
ini_set('post_max_size', '1G');
ini_set('memory_limit', '2048M');
ini_set('max_execution_time', 300);

use Slim\App;
use UMA\DIC\Container;
use FpvJp\DI;

/** @var Container $cnt */
$cnt = require_once __DIR__ . '/../bootstrap.php';

$cnt->register(new DI\Doctrine());
$cnt->register(new DI\CloudinaryAdmin());
$cnt->register(new DI\Wasabi());
$cnt->register(new DI\Mailer());
$cnt->register(new DI\Slim());

/** @var App $app */
$app = $cnt->get(App::class);
$app->run();
