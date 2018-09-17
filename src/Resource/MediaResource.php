<?php
/**
 * kiwi-suite/admin (https://github.com/kiwi-suite/media)
 *
 * @package   kiwi-suite/media
 * @see       https://github.com/kiwi-suite/media
 * @copyright Copyright (c) 2010 - 2018 kiwi suite GmbH
 * @license   MIT License
 */
declare(strict_types=1);
namespace KiwiSuite\Media\Resource;
use KiwiSuite\Admin\Resource\DefaultAdminTrait;
use KiwiSuite\Contract\Resource\AdminAwareInterface;
use KiwiSuite\Contract\Schema\BuilderInterface;
use KiwiSuite\Contract\Schema\ElementInterface;
use KiwiSuite\Contract\Schema\Listing\ListSchemaInterface;
use KiwiSuite\Contract\Schema\SchemaInterface;
use KiwiSuite\Media\Action\IndexAction;
use KiwiSuite\Media\Action\Media\DetailAction;
use KiwiSuite\Media\Action\Media\UpdateAction;
use KiwiSuite\Media\Entity\Media;
use KiwiSuite\Media\Repository\MediaRepository;
use KiwiSuite\Schema\Builder;
use KiwiSuite\Schema\Elements\CheckboxElement;
use KiwiSuite\Schema\Elements\ImageElement;
use KiwiSuite\Schema\Listing\ListElement;
use KiwiSuite\Schema\Listing\ListSchema;
use KiwiSuite\Schema\Schema;
final class MediaResource implements AdminAwareInterface
{
    use DefaultAdminTrait;
    public function label(): string
    {
        return 'Media';
    }
    /**
     * @return null|string
     */
    public function indexAction(): ?string
    {
        return IndexAction::class;
    }
    public static function serviceName(): string
    {
        return "media";
    }
    /**
     * @return string
     */
    public function repository(): string
    {
        return MediaRepository::class;
    }
    /**
     * @return null|string
     */
    public function detailAction(): ?string
    {
        return DetailAction::class;
    }
    /**
     * @param BuilderInterface $builder
     * @return SchemaInterface
     */
    public function createSchema(BuilderInterface $builder): SchemaInterface
    {
        return new Schema();
    }
    /**
     * @param BuilderInterface $builder
     * @return SchemaInterface
     */
    public function updateSchema(BuilderInterface $builder): SchemaInterface
    {
        /** @var SchemaInterface $schema */
        $schema = $builder->fromEntity(Media::class);
        $element = $builder->create(CheckboxElement::class, 'publicStatus');
        $element = $element->withLabel('Declare Public?');
        $schema = $schema->withAddedElement($element);
        $schema = $schema->remove('filename');
        $schema = $schema->remove('hash');
        $schema = $schema->remove('deletedAt');
        $schema = $schema->remove('size');
        $schema = $schema->remove('mimeType');
        $schema = $schema->remove('basePath');
        return $schema;
    }
    /**
     * @return ListSchemaInterface
     */
    public function listSchema(): ListSchemaInterface
    {
        return (new ListSchema())->withDefaultSorting('createdAt', 'DESC');
    }
}