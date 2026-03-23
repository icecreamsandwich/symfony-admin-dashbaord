<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260323175000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create firmware version table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('firmware_version');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('system_version', 'string', ['length' => 255]);
        $table->addColumn('system_version_alt', 'string', ['length' => 255]);
        $table->addColumn('link', 'string', ['length' => 255]);
        $table->addColumn('st', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('gd', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('latest', 'boolean');
        $table->addColumn('created_at', 'datetime_immutable');
        $table->addColumn('updated_at', 'datetime_immutable');
        $table->setPrimaryKey(['id']);
        $table->addIndex(['name'], 'IDX_F89658AB7E237E06');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('firmware_version');
    }
}
