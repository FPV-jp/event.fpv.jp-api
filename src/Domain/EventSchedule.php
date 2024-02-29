<?php declare(strict_types=1);

namespace FpvJp\Domain;

use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

use Doctrine\DBAL\Types\Types;
use JsonSerializable;

// The EventSchedule class demonstrates how to annotate a simple PHP class to act as a Doctrine entity.

#[Entity, Table(name: 'event_schedules')]
final class EventSchedule implements JsonSerializable
{
    #[Id, Column(type: 'integer'), GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[Column(name: 'create_user', type: Types::STRING, unique: false, nullable: false)]
    private string $create_user;

    #[Column(name: 'event_title', type: Types::STRING, unique: false, nullable: false)]
    private string $event_title;

    #[Column(name: 'event_color', type: Types::STRING, unique: false, nullable: false)]
    private string $event_color;

    #[Column(name: 'start_datetime', type: Types::DATETIMETZ_IMMUTABLE, unique: false, nullable: false)]
    private DateTimeImmutable $start_datetime;

    #[Column(name: 'end_datetime', type: Types::DATETIMETZ_IMMUTABLE, unique: false, nullable: true)]
    private DateTimeImmutable $end_datetime;

    #[Column(name: 'all_day', type: Types::BOOLEAN, nullable: false)]
    private bool $all_day;

    #[Column(name: 'registered_at', type: Types::DATETIMETZ_IMMUTABLE, nullable: false)]
    private DateTimeImmutable $registeredAt;

    public function __construct(array $eventSchedule, array $token)
    {
        error_log(print_r($eventSchedule, true));
        $this->create_user = $token['email'];
        $this->event_title = $eventSchedule['event_title'];
        $this->event_color = $eventSchedule['event_color'];
        $this->start_datetime = new DateTimeImmutable($eventSchedule['start_datetime']);
        if (isset($eventSchedule['end_datetime'])) {
            $this->end_datetime = new DateTimeImmutable($eventSchedule['end_datetime']);
        }
        $this->all_day = $eventSchedule['all_day'];
        $this->registeredAt = new DateTimeImmutable('now');
    }
    public function getId(): int
    {
        return $this->id;
    }

    public function getCreateUser(): string
    {
        return $this->create_user;
    }

    public function getEventTitle(): string
    {
        return $this->event_title;
    }

    public function getEventColor(): string
    {
        return $this->event_color;
    }

    public function getStartDatetime(): DateTimeImmutable
    {
        return $this->start_datetime;
    }

    public function getEndDatetime(): DateTimeImmutable | null
    {
        return $this->end_datetime;
    }

    public function getAllDay(): bool
    {
        return $this->all_day;
    }

    public function getRegisteredAt(): DateTimeImmutable
    {
        return $this->registeredAt;
    }

    public function updateParameters($args)
    {
        // if (isset($args['name'])) {
        //     $this->name = $args['name'];
        // }
        if (isset($args['email'])) {
            $this->email = $args['email'];
        }
        if (isset($args['password'])) {
            $this->hash = password_hash($args['password'], PASSWORD_BCRYPT);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'create_user' => $this->getCreateUser(),
            'event_title' => $this->getEventTitle(),
            'event_color' => $this->getEventColor(),
            'start_datetime' => $this->getStartDatetime()->format(DateTimeImmutable::ATOM),
            'end_datetime' => isset($end_datetime) ? $this->getEndDatetime()->format(DateTimeImmutable::ATOM) : null,
            'all_day' => $this->getAllDay(),
            'registered_at' => $this->getRegisteredAt()->format(DateTimeImmutable::ATOM)
        ];
    }
}
