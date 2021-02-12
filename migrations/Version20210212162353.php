<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210212162353 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE seat_asset DROP FOREIGN KEY FK_A21B8412C1DAFE35');
        $this->addSql('ALTER TABLE seat_asset ADD CONSTRAINT FK_D533D178C1DAFE35 FOREIGN KEY (seat_id) REFERENCES seat (id) ON DELETE CASCADE');
    }

    public function isTransactional(): bool
    {
        return false;
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE seat_asset DROP FOREIGN KEY FK_D533D178C1DAFE35');
        $this->addSql('ALTER TABLE seat_asset ADD CONSTRAINT FK_A21B8412C1DAFE35 FOREIGN KEY (seat_id) REFERENCES seat (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
