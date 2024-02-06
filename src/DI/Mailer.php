<?php

declare(strict_types=1);

namespace UMA\FpvJpApi\DI;

use Psr\Container\ContainerInterface;

use Doctrine\DBAL\{
    Logging\SQLLogger,
    DriverManager
};
use Doctrine\ORM\{
    EntityManager,
    ORMSetup
};
use PHPMailer\PHPMailer\{
    Exception,
    PHPMailer
};
use Symfony\Component\Cache\Adapter\{
    ArrayAdapter,
    FilesystemAdapter
};
use UMA\DIC\{
    Container,
    ServiceProvider
};

final class Mailer implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function provide(Container $c): void
    {
        $c->set(PHPMailer::class, static function (ContainerInterface $c): PHPMailer {
            /** @var array $settings */
            $settings = $c->get('settings');

            $mail = new PHPMailer(true);

            // Server settings
            // $mail->SMTPDebug = $settings['debug'];
            $mail->isSMTP();
            // $mail->Host = $settings['host'];
            // $mail->SMTPAuth = (bool)$settings['auth'];
            // $mail->Username = $settings['username'];
            // $mail->Password = $settings['password'];
            // $mail->SMTPSecure = $settings['password'];
            // $mail->Port = (int)$settings['port'];
            $mail->Host = 'v2008.coreserver.jp';
            $mail->SMTPAuth = true;
            $mail->Username = 'fpv@fpv.jp';
            $mail->Password = '';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 465;
            return $mail;
        });
    }
}

        // try {
        //     // Server settings
        //     // $mail = new PHPMailer(true);
        //     // $mail->isSMTP();
            // $mail->Host = 'v2008.coreserver.jp';
            // $mail->SMTPAuth = true;
            // $mail->Username = 'fpv@fpv.jp';
            // $mail->Password = '';
            // $mail->SMTPSecure = 'tls';
            // $mail->Port = 465;

        //     // // Recipients
        //     // $mail->setFrom('fpv@fpv.jp', 'Your Name');
        //     // $mail->addAddress('tantaka.tomokazu@gmail.com', 'Recipient Name');

        //     // // Content
        //     // $mail->isHTML(true);
        //     // $mail->Subject = 'Subject of the Email';
        //     // $mail->Body = 'This is the HTML message body';

        //     // $mail->send();
        //     // error_log('Message has been sent');

            
        // } catch (Exception $e) {
        //     echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        //     error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        //     var_dump($mail->ErrorInfo);
        // }
