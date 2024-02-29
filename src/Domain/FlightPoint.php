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

// The FlightPoint class demonstrates how to annotate a simple PHP class to act as a Doctrine entity.

#[Entity, Table(name: 'flight_points')]
final class FlightPoint implements JsonSerializable
{
    #[Id, Column(type: Types::INTEGER), GeneratedValue(strategy: 'AUTO')]
    private int $id;
    #[Column(name: 'latitude', type: Types::FLOAT, unique: false, nullable: false)]
    private float $latitude;
    
    #[Column(name: 'longitude', type: Types::FLOAT, unique: false, nullable: false)]
    private float $longitude;

    #[Column(name: 'title', type: Types::STRING, unique: false, nullable: false)]
    private string $title;

    #[Column(name: 'create_user', type: Types::STRING, unique: false, nullable: false)]
    private string $create_user;

    #[Column(name: 'marker_image', type: Types::STRING, unique: false, nullable: false)]
    private string $marker_image;

    #[Column(name: 'registered_at', type: Types::DATETIMETZ_IMMUTABLE, nullable: false)]
    private DateTimeImmutable $registeredAt;

    public function __construct(array $flightPoint, array $token)
    {
        $this->latitude = $flightPoint['latitude'];
        $this->longitude = $flightPoint['longitude'];
        $this->title = $flightPoint['title'];
        $this->create_user = $token['email'];
        $this->marker_image = $flightPoint['marker_image'];
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
    public function getTitle(): string
    {
        return $this->title;
    }
    public function getCreateUser(): string
    {
        return $this->create_user;
    }
    public function getMarkerImage(): string
    {
        return $this->marker_image;
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
            'latitude' => $this->getLatitude(),
            'longitude' => $this->getLongitude(),
            'title' => $this->getTitle(),
            'create_user' => $this->getCreateUser(),
            'marker_image' => $this->getMarkerImage(),
            'registered_at' => $this->getRegisteredAt()->format(DateTimeImmutable::ATOM)
        ];
    }
}
