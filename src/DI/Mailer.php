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
            $mailer->SMTPAuth = $settings['mail']['auth'];
            $mailer->Host = $settings['mail']['host'];
            $mailer->Username = $settings['mail']['username'];
            $mailer->Password = $settings['mail']['password'];
            $mailer->Port = $settings['mail']['port'];
            $mailer->setFrom($settings['mail']['username'], $settings['mail']['sender']);
            // $mailer->SMTPSecure = $settings['mail']['encryption'];

            return $mailer;
        });
    }
}
