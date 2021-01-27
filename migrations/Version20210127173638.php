<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210127173638 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE webhook (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, active TINYINT(1) NOT NULL, webhook_url VARCHAR(255) NOT NULL, INDEX IDX_8A741756A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE webhook ADD CONSTRAINT FK_8A741756A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function isTransactional(): bool
    {
        return false;
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE webhook');
    }
}
