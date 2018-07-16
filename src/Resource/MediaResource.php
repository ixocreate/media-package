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
use KiwiSuite\Contract\Schema\Listing\ListSchemaInterface;
use KiwiSuite\Contract\Schema\SchemaInterface;
use KiwiSuite\Media\Repository\MediaRepository;
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
        return null;
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
        return new Schema();
    }

    /**
     * @return ListSchemaInterface
     */
    public function listSchema(): ListSchemaInterface
    {
        return (new ListSchema())->withDefaultSorting('createdAt', 'DESC');
    }
}
