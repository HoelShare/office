<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201230191845 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE seat ADD floor_id INT NOT NULL');
        $this->addSql('ALTER TABLE seat ADD CONSTRAINT FK_3D5C3666854679E2 FOREIGN KEY (floor_id) REFERENCES floor (id)');
        $this->addSql('ALTER TABLE floor CHANGE floor_path floor_path VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_3D5C3666854679E2 ON seat (floor_id)');
    }

    public function isTransactional(): bool
    {
        return false;
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE seat DROP FOREIGN KEY FK_3D5C3666854679E2');
        $this->addSql('DROP INDEX IDX_3D5C3666854679E2 ON seat');
        $this->addSql('ALTER TABLE seat DROP floor_id');
        $this->addSql('ALTER TABLE floor CHANGE floor_path floor_path VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
