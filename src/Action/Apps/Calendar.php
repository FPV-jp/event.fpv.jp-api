<?php

declare(strict_types=1);

namespace UMA\FpvJpApi\Action\Apps;

use Doctrine\ORM\EntityManager;
use Faker\Generator;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\Stream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use UMA\FpvJpApi\Domain\User;
use function json_encode;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

final class Calendar implements RequestHandlerInterface
{
    private EntityManager $em;
    private Generator $faker;
    private PHPMailer $mail2;

    public function __construct(EntityManager $em, Generator $faker, PHPMailer $mail)
    {
        $this->em = $em;
        $this->faker = $faker;
        $this->mail2 = $mail;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var User[] $users */
        $users = $this->em
            ->getRepository(User::class)
            ->findAll();

        $curYear = date('Y');
        $curMonth = date('m');

        $calendarEvents = [
            [
                'backgroundColor' => '#FFC400',
                'borderColor' => '#FFC400',
                'title' => '9:30 AM - 8:00 PM Awwards Conference',
                'start' => "$curYear-$curMonth-04",
                'end' => "$curYear-$curMonth-06",
            ],
            [
                'backgroundColor' => '#da82f8',
                'borderColor' => '#da82f8',
                'title' => 'Jampack Team Meet',
                'start' => "$curYear-$curMonth-13",
                'end' => "$curYear-$curMonth-15",
            ],
            [
                'backgroundColor' => '#da82f8',
                'borderColor' => '#da82f8',
                'title' => 'Project meeting with delegates',
                'start' => "$curYear-$curMonth-19",
            ],
            [
                'backgroundColor' => '#298DFF',
                'borderColor' => '#298DFF',
                'title' => 'Conference',
                'start' => "$curYear-$curMonth-11",
                'end' => "$curYear-$curMonth-13",
            ],
            [
                'title' => 'Call back to Morgan Freeman',
                'start' => "$curYear-$curMonth-27T10:30:00",
            ],
            [
                'title' => 'Grocery Day',
                'start' => "$curYear-$curMonth-27T12:00:00",
            ],
            [
                'title' => 'Follow-up call with client',
                'start' => "$curYear-$curMonth-7T14:30:00",
            ],
            [
                'title' => 'Follow-up call with client',
                'start' => "$curYear-$curMonth-07T07:00:00",
            ],
            [
                'title' => 'Grocery Day',
                'start' => "$curYear-$curMonth-02T07:00:00",
            ],
            [
                'backgroundColor' => '#298DFF',
                'borderColor' => '#298DFF',
                'title' => '✈ 2:35 PM Flight to Indonesia',
                'start' => "$curYear-$curMonth-13",
            ],
            [
                'backgroundColor' => '#007D88',
                'borderColor' => '#007D88',
                'title' => "🎂 Boss's Birthday",
                'start' => "$curYear-$curMonth-29",
            ],
        ];

        $body = Stream::create(json_encode($calendarEvents, JSON_PRETTY_PRINT) . PHP_EOL);

        return new Response(200, ['Content-Type' => 'application/json'], $body);
    }
}
