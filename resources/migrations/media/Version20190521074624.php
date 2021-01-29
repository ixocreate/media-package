<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Ixocreate\Schema\Type\DateTimeType;
use Ixocreate\Schema\Type\UuidType;

final class Version20190521074624 extends AbstractMigration
{
    /**
     * @param Schema $schema
     * @throws \Doctrine\DBAL\DBALException
     */
    public function preUp(Schema $schema): void
    {
        $this->connection->executeQuery('RENAME TABLE `media_media_crop` TO `media_definition_info`');
        $this->connection->executeQuery('ALTER TABLE `media_media` CHANGE `size` `fileSize` int(11)');
    }

    public function up(Schema $schema): void
    {
        $mediaImageInfo = $schema->getTable('media_definition_info');
        $mediaImageInfo->dropPrimaryKey();
        $mediaImageInfo->dropColumn('id');
        $mediaImageInfo->addColumn('width', Types::INTEGER);
        $mediaImageInfo->addColumn('height', Types::INTEGER);
        $mediaImageInfo->addColumn('fileSize', Types::INTEGER);
        $mediaImageInfo->getColumn('cropParameters')->setNotnull(false);
        $mediaImageInfo->setPrimaryKey(['mediaId', 'imageDefinition']);
        foreach ($mediaImageInfo->getForeignKeys() as $foreignKeyName => $foreignKey) {
            $mediaImageInfo->removeForeignKey($foreignKeyName);
        }

        $media = $schema->getTable('media_media');
        $media->getColumn('fileSize')->setNotnull(true);
        $media->addColumn('metaData', Types::JSON)->setNotnull(false);
        $media->addColumn('createdBy', UuidType::serviceName())->setNotNull(false);
        $media->addColumn('deletedAt', DateTimeType::serviceName())->setNotnull(false);

        $schema->dropTable('media_media_created');
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable('media_definition_info');
    }
}
