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

final class Version20180330152703 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $table = $schema->createTable('media_media');
        $table->addColumn('id', UuidType::serviceName());
        $table->addColumn('basePath', Type::STRING);
        $table->addColumn('filename', Type::STRING);
        $table->addColumn('mimeType', Type::STRING);
        $table->addColumn('size', Type::INTEGER);
        $table->addColumn('hash', Type::STRING);
        $table->addColumn('publicStatus', Type::BOOLEAN);
        $table->addColumn('createdAt', DateTimeType::serviceName());
        $table->addColumn('updatedAt', DateTimeType::serviceName());
        $table->setPrimaryKey(["id"]);
    }

    public function down(Schema $schema)
    {
        $schema->dropTable("media_media");
    }
}
