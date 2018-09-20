<?php
declare(strict_types=1);

namespace KiwiSuite\Media\Entity;


use Doctrine\DBAL\Types\Type;
use KiwiSuite\Entity\Entity\DefinitionCollection;
use KiwiSuite\Entity\Entity\EntityInterface;
use KiwiSuite\Entity\Entity\EntityTrait;
use KiwiSuite\Entity\Entity\Definition;
use KiwiSuite\CommonTypes\Entity\DateTimeType;
use KiwiSuite\Contract\Type\TypeInterface;
use KiwiSuite\CommonTypes\Entity\UuidType;


final class MediaCrop implements EntityInterface
{
    use EntityTrait;

    private $id;
    private $mediaId;
    private $imageDefinition;
    private $cropParameters;
    private $createdAt;
    private $updatedAt;

    public function id(): UuidType
    {
        return $this->id;
    }

    public function mediaId(): UuidType
    {
        return $this->mediaId;
    }

    public function imageDefinition(): string
    {
        return $this->imageDefinition;
    }

    public function cropParameters(): ?array
    {
        return $this->cropParameters;
    }

    public function createdAt(): DateTimeType
    {
        return $this->createdAt;
    }

    public function updatedAt(): DateTimeType
    {
        return $this->updatedAt;
    }

    /**
     * @return DefinitionCollection
     */
    protected static function createDefinitions(): DefinitionCollection
    {
        return new DefinitionCollection([
            new Definition('id', UuidType::class, false, true),
            new Definition('mediaId', UuidType::class, false, true),
            new Definition('imageDefinition', TypeInterface::TYPE_STRING, false,true),
            new Definition('cropParameters',TypeInterface::TYPE_ARRAY,false,true),
            new Definition('createdAt', DateTimeType::class, false, true),
            new Definition('updatedAt', DateTimeType::class, false, true),
        ]);
    }
}