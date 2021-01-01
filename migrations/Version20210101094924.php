<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210101094924 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE seat_asset CHANGE `order` priority INT NOT NULL');
        $this->addSql('ALTER TABLE seat_asset RENAME INDEX idx_a21b8412c1dafe35 TO IDX_D533D178C1DAFE35');
        $this->addSql('ALTER TABLE seat_asset RENAME INDEX idx_a21b841289329d25 TO IDX_D533D1785DA1941');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE seat_asset CHANGE priority `order` INT NOT NULL');
        $this->addSql('ALTER TABLE seat_asset RENAME INDEX idx_d533d1785da1941 TO IDX_A21B841289329D25');
        $this->addSql('ALTER TABLE seat_asset RENAME INDEX idx_d533d178c1dafe35 TO IDX_A21B8412C1DAFE35');
    }
}
