<?php declare(strict_types = 1);

namespace KiwiMigration;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use KiwiSuite\CommonTypes\Entity\DateTimeType;
use KiwiSuite\CommonTypes\Entity\UuidType;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180330152703 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $table = $schema->createTable('media_media');
        $table->addColumn('id', UuidType::class);
        $table->addColumn('basePath', Type::STRING);
        $table->addColumn('filename', Type::STRING);
        $table->addColumn('mimeType', Type::STRING);
        $table->addColumn('size', Type::INTEGER);
        $table->addColumn('publicStatus',Type::BOOLEAN);
        $table->addColumn('hash', Type::STRING);
        $table->addColumn('createdAt', DateTimeType::class);
        $table->addColumn('updatedAt', DateTimeType::class);
        $table->addColumn('deletedAt', DateTimeType::class)->setNotnull(false);
        $table->setPrimaryKey(["id"]);
        $table->addUniqueIndex(["hash"]);
    }

    public function down(Schema $schema)
    {
        $schema->dropTable("media_media");
    }
}
