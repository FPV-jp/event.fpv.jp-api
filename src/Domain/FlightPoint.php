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
use function password_hash;

// The FlightPoint class demonstrates how to annotate a simple PHP class to act as a Doctrine entity.

#[Entity, Table(name: 'flight_points')]
final class FlightPoint implements JsonSerializable
{
    #[Id, Column(type: Types::INTEGER), GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[Column(name: 'latitude', type: Types::FLOAT, unique: true, nullable: false)]
    private float $latitude;

    #[Column(name: 'longitude', type: Types::FLOAT, unique: true, nullable: false)]
    private float $longitude;

    #[Column(name: 'user', type: Types::STRING, unique: true, nullable: false)]
    private string $user;

    #[Column(name: 'email', type: Types::STRING, unique: true, nullable: false)]
    private string $email;

    #[Column(name: 'registered_at', type: Types::DATETIMETZ_IMMUTABLE, nullable: false)]
    private DateTimeImmutable $registeredAt;

    public function __construct(float $latitude, float $longitude, string $email, string $user)
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->user = $user;
        $this->email = $email;
        $this->registeredAt = new DateTimeImmutable('now');
    }

    public function getId(): int
    {
        return $this->id;
    }
    public function getLatitude(): float
    {
        return $this->latitude;
    }
    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function getUser(): string
    {
        return $this->user;
    }

    public function getEmail(): string
    {
        return $this->email;
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

    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'email' => $this->getEmail(),
            'registered_at' => $this->getRegisteredAt()->format(DateTimeImmutable::ATOM)
        ];
    }
}
