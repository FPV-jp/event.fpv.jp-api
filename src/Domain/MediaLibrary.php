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

// The MediaLibrary class demonstrates how to annotate a simple PHP class to act as a Doctrine entity.

#[Entity, Table(name: 'media_libraries')]
final class MediaLibrary implements JsonSerializable
{
    #[Id, Column(type: Types::INTEGER), GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[Column(name: 'owner', type: Types::STRING, unique: false, nullable: false)]
    private string $owner;

    #[Column(name: 'is_public', type: Types::BOOLEAN, options: ['default' => true], nullable: false)]
    private bool $is_public;

    #[Column(name: 'file_name', type: Types::STRING, unique: false, nullable: false)]
    private string $file_name;

    #[Column(name: 'file_type', type: Types::STRING, unique: false, nullable: false)]
    private string $file_type;

    #[Column(name: 'file_size', type: Types::INTEGER, nullable: false)]
    private int $file_size;

    #[Column(name: 'wasabi_file_key', type: Types::STRING, unique: false, nullable: false)]
    private string $wasabi_file_key;

    #[Column(name: 'registered_at', type: Types::DATETIMETZ_IMMUTABLE, nullable: false)]
    private DateTimeImmutable $registeredAt;

    public function __construct(array $mediaLibrary, array $token)
    {
        $this->owner = $token['email'];
        $this->is_public = false;
        $this->file_name = $mediaLibrary['file_name'];
        $this->file_type = $mediaLibrary['file_type'];
        $this->file_name = $mediaLibrary['file_name'];
        $this->file_size = $mediaLibrary['file_size'];
        $this->wasabi_file_key = $mediaLibrary['wasabi_file_key'];
        $this->registeredAt = new DateTimeImmutable('now');
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function isPublic(): bool
    {
        return $this->is_public;
    }

    public function getFileName(): string
    {
        return $this->file_name;
    }

    public function getFileType(): string
    {
        return $this->file_type;
    }

    public function getFileSize(): int
    {
        return $this->file_size;
    }

    public function getWasabiFileKey(): string
    {
        return $this->wasabi_file_key;
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
            'is_public' => $this->isPublic(),
            'file_name' => $this->getFileName(),
            'file_type' => $this->getFileType(),
            'file_size' => $this->getFileSize(),
            'wasabi_file_key' => $this->getWasabiFileKey(),
            'registered_at' => $this->getRegisteredAt()->format(DateTimeImmutable::ATOM)
        ];
    }
}
