<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\Migrations\AbstractMigration;
use Ixocreate\Schema\Type\DateTimeType;

final class Version20190521074624 extends AbstractMigration
{

    public function preUp(Schema $schema)
    {
        $this->connection->executeQuery('ALTER TABLE `media_media_crop` RENAME `media_image_info`');
        $this->connection->executeQuery('ALTER TABLE `media_media` CHANGE `size` `fileSize` int(11)');
    }

    public function up(Schema $schema) : void
    {
        // MEDIA-IMAGE-INFO
        $mediaImageInfo = $schema->getTable('media_image_info');
        $mediaImageInfo->dropPrimaryKey();
        $mediaImageInfo->dropColumn('id');
        $mediaImageInfo->addColumn('width', Type::INTEGER);
        $mediaImageInfo->addColumn('height', Type::INTEGER);
        $mediaImageInfo->addColumn('fileSize', Type::INTEGER);
        $mediaImageInfo->setPrimaryKey(['mediaId', 'imageDefinition']);
        $mediaImageInfo->addForeignKeyConstraint('media_media', ['mediaId'], ['id'], ['onDelete' => 'CASCADE']);

        // MEDIA
        $media = $schema->getTable('media_media');
        $media->addColumn('metaData', Type::JSON);
        $media->addColumn('deletedAt', DateTimeType::serviceName())->setNotnull(false);
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable('media_image_info');
    }
}
