<?php declare(strict_types=1);

namespace FpvJp\DI;

use Psr\Container\ContainerInterface;
use PHPMailer\PHPMailer\PHPMailer;
use UMA\DIC\Container;
use UMA\DIC\ServiceProvider;

class Mailer implements ServiceProvider
{
    public function provide(Container $c): void
    {
        $c->set(PHPMailer::class, static function (ContainerInterface $c): PHPMailer {

            /** @var array $settings */
            $settings = $c->get('settings');

            $mailer = new PHPMailer(true);
            $mailer->isSMTP();
            $mailer->SMTPAuth = $settings['mai']['auth'];
            $mailer->Host = $settings['mai']['host'];
            $mailer->Username = $settings['mai']['username'];
            $mailer->Password = $settings['mai']['password'];
            $mailer->Port = $settings['mai']['port'];
            $mailer->setFrom($settings['mai']['username'], $settings['mai']['sender']);
            // $mailer->SMTPSecure = $settings['mai']['encryption'];

            return $mailer;
        });
    }
}
