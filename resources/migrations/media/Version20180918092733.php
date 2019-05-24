<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\Migrations\AbstractMigration;
use Ixocreate\Schema\Type\DateTimeType;
use Ixocreate\Schema\Type\UuidType;

final class Version20180918092733 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $table = $schema->createTable('media_media_crop');
        $table->addColumn('id', UuidType::serviceName());
        $table->addColumn('mediaId', UuidType::serviceName());
        $table->addColumn('imageDefinition', Type::STRING);
        $table->addColumn('cropParameters', Type::JSON);
        $table->addColumn('createdAt', DateTimeType::serviceName());
        $table->addColumn('updatedAt', DateTimeType::serviceName());
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('media_media', ['mediaId'], ['id'], ['onDelete' => 'CASCADE']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable("media_media_crop");
    }
}
