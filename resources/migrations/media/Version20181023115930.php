<?php
declare(strict_types=1);

namespace Ixocreate\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Ixocreate\Package\Type\Entity\UuidType;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181023115930 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $table = $schema->createTable('media_media_created');
        $table->addColumn('mediaId', UuidType::serviceName());
        $table->addColumn('createdBy', UuidType::serviceName());
        $table->setPrimaryKey(['mediaId']);
        $table->addForeignKeyConstraint('media_media', ['mediaId'], ['id'], ['onDelete' => 'CASCADE']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable("media_media_created");
    }
}
