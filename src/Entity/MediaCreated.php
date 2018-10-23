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


final class MediaCreated implements EntityInterface
{
    use EntityTrait;

    private $mediaId;
    private $createdBy;

    public function mediaId(): UuidType
    {
        return $this->mediaId;
    }

    public function createdBy(): UuidType
    {
        return $this->createdBy;
    }

    /**
     * @return DefinitionCollection
     */
    protected static function createDefinitions(): DefinitionCollection
    {
        return new DefinitionCollection([
            new Definition('mediaId', UuidType::class, false, true),
            new Definition('createdBy', UuidType::class, false, true),
        ]);
    }
}