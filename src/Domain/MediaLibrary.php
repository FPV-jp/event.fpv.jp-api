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

    #[Column(name: 'file_width', type: Types::INTEGER, nullable: true)]
    private int|null $file_width = null;

    #[Column(name: 'file_height', type: Types::INTEGER, nullable: true)]
    private int|null $file_height = null;

    #[Column(name: 'file_duration', type: Types::FLOAT, nullable: true)]
    private float|null $file_duration = null;

    #[Column(name: 'file_last_modified', type: Types::DATETIMETZ_IMMUTABLE, nullable: false)]
    private DateTimeImmutable $file_last_modified;

    #[Column(name: 'wasabi_file_key', type: Types::STRING, unique: false, nullable: false)]
    private string $wasabi_file_key;

    #[Column(name: 'registered_at', type: Types::DATETIMETZ_IMMUTABLE, nullable: false)]
    private DateTimeImmutable $registeredAt;

    public function __construct(array $mediaLibrary, array $token)
    {
        error_log(print_r($mediaLibrary, true));
        $this->owner = $token['email'];
        $this->is_public = false;
        $this->file_name = $mediaLibrary['file_name'];
        $this->file_type = $mediaLibrary['file_type'];
        $this->file_name = $mediaLibrary['file_name'];
        $this->file_size = $mediaLibrary['file_size'];
        if (isset($mediaLibrary['file_width'])) {
            $this->file_width = $mediaLibrary['file_width'];
        }
        if (isset($mediaLibrary['file_height'])) {
            $this->file_height = $mediaLibrary['file_height'];
        }
        if (isset($mediaLibrary['file_duration'])) {
            $this->file_duration = $mediaLibrary['file_duration'];
        }
        $this->file_last_modified = new DateTimeImmutable($mediaLibrary['file_last_modified']);
        $this->wasabi_file_key = $mediaLibrary['wasabi_file_key'];
        $this->registeredAt = new DateTimeImmutable('now');
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getOwner(): string
    {
        return $this->owner;
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

    public function getFileWidth(): int|null
    {
        return $this->file_width;
    }

    public function getFileHeight(): int|null
    {
        return $this->file_height;
    }

    public function getFileDuration(): float|null
    {
        return $this->file_duration;
    }

    public function getFileLastModified(): DateTimeImmutable
    {
        return $this->file_last_modified;
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
        $data = [
            'id' => $this->getId(),
            'owner' => $this->getOwner(),
            'is_public' => $this->isPublic(),
            'file_name' => $this->getFileName(),
            'file_type' => $this->getFileType(),
            'file_size' => $this->getFileSize(),
            'file_width' => $this->getFileWidth(),
            'file_height' => $this->getFileHeight(),
            'file_duration' => $this->getFileDuration(),
            'file_last_modified' => $this->getFileLastModified()->format(DateTimeImmutable::ATOM),
            'wasabi_file_key' => $this->getWasabiFileKey(),
            'registered_at' => $this->getRegisteredAt()->format(DateTimeImmutable::ATOM),
        ];
        foreach ($data as $key => $value) {
            if ($value === null) {
                unset($data[$key]);
            }
        }
        return $data;
    }
}
