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

final class Version20180330152703 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $table = $schema->createTable('media_media');
        $table->addColumn('id', UuidType::serviceName());
        $table->addColumn('basePath', Types::STRING);
        $table->addColumn('filename', Types::STRING);
        $table->addColumn('mimeType', Types::STRING);
        $table->addColumn('size', Types::INTEGER);
        $table->addColumn('hash', Types::STRING);
        $table->addColumn('publicStatus', Types::BOOLEAN);
        $table->addColumn('createdAt', DateTimeType::serviceName());
        $table->addColumn('updatedAt', DateTimeType::serviceName());

        $table->setPrimaryKey(['id']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('media_media');
    }
}
