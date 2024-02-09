<?php declare(strict_types=1);

namespace UMA\FpvJpApi\Action;

use Doctrine\ORM\EntityManager;
use Faker\Generator;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\Stream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use UMA\FpvJpApi\Domain\User;
use PHPMailer\PHPMailer\{
    Exception,
    PHPMailer
};

use Slim\Exception\HttpInternalServerErrorException;

use function json_encode;

final class CreateUser implements RequestHandlerInterface
{
    private EntityManager $em;
    private PHPMailer $mail;
    private Generator $faker;

    public function __construct(EntityManager $em, PHPMailer $mail, Generator $faker)
    {
        $this->em = $em;
        $this->mail = $mail;
        $this->faker = $faker;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $newRandomUser = new User($this->faker->email(), $this->faker->password());

        $this->em->persist($newRandomUser);
        $this->em->flush();

        try {
            
            // Recipients
            $this->mail->addAddress('tantaka.tomokazu@gmail.com', 'Recipient Name');

            // Content
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Subject of the Email';
            $this->mail->Body = '<!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>HTMLメールの例</title>
            </head>
            <body>
                <div style="background-color: #f0f0f0; padding: 20px;">
                    <h1 style="color: #333333;">HTMLメールの例</h1>
                    <p style="color: #555555;">これはHTML形式のメールの例です。</p>
                    <p style="color: #555555;">以下は、リストの例です。</p>
                    <ul>
                        <li>項目1</li>
                        <li>項目2</li>
                        <li>項目3</li>
                    </ul>
                    <p style="color: #555555;">以下は、リンクの例です。</p>
                    <p><a href="https://www.example.com" style="color: #007bff;">例のウェブサイトへ</a></p>
                    <p style="color: #555555;">画像の例:</p>
                    <img src="https://via.placeholder.com/150" alt="Placeholder Image" style="display: block; margin: 10px 0;">
                    <p style="color: #555555;">以上がHTMLメールの例です。</p>
                </div>
            </body>
            </html>';
            $this->mail->send();
        } catch (Exception $e) {
            throw new HttpInternalServerErrorException($request, $this->mail->ErrorInfo);
        }

        $body = Stream::create(json_encode($newRandomUser, JSON_PRETTY_PRINT) . PHP_EOL);

        return new Response(201, ['Content-Type' => 'application/json'], $body);
    }
}
