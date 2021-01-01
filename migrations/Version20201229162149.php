<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201229162149 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE booking (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, seat_id INT NOT NULL, from_day DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', until_day DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_E00CEDDEA76ED395 (user_id), INDEX IDX_E00CEDDEC1DAFE35 (seat_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE building (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, city VARCHAR(255) DEFAULT NULL, post_code VARCHAR(255) DEFAULT NULL, street VARCHAR(255) DEFAULT NULL, country_code VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE floor (id INT AUTO_INCREMENT NOT NULL, building_id INT NOT NULL, name VARCHAR(255) DEFAULT NULL, number INT DEFAULT NULL, floor_path VARCHAR(255) NOT NULL, INDEX IDX_BE45D62E4D2A7E12 (building_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE asset (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE seat (id INT AUTO_INCREMENT NOT NULL, number INT NOT NULL, location_x DOUBLE PRECISION NOT NULL, location_y DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE seat_asset (id INT AUTO_INCREMENT NOT NULL, seat_id INT NOT NULL, asset_id INT NOT NULL, `order` INT NOT NULL, INDEX IDX_A21B8412C1DAFE35 (seat_id), INDEX IDX_A21B841289329D25 (asset_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDEA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDEC1DAFE35 FOREIGN KEY (seat_id) REFERENCES seat (id)');
        $this->addSql('ALTER TABLE floor ADD CONSTRAINT FK_BE45D62E4D2A7E12 FOREIGN KEY (building_id) REFERENCES building (id)');
        $this->addSql('ALTER TABLE seat_asset ADD CONSTRAINT FK_A21B8412C1DAFE35 FOREIGN KEY (seat_id) REFERENCES seat (id)');
        $this->addSql('ALTER TABLE seat_asset ADD CONSTRAINT FK_A21B841289329D25 FOREIGN KEY (asset_id) REFERENCES asset (id)');
        $this->addSql('ALTER TABLE ldap_token CHANGE expire expire DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE user ADD image LONGTEXT DEFAULT NULL, DROP active, CHANGE ldap_id ldap_id VARCHAR(255) NOT NULL, CHANGE email email VARCHAR(255) DEFAULT NULL, CHANGE name name VARCHAR(255) DEFAULT NULL, CHANGE full_name full_name VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE floor DROP FOREIGN KEY FK_BE45D62E4D2A7E12');
        $this->addSql('ALTER TABLE seat_resource DROP FOREIGN KEY FK_A21B841289329D25');
        $this->addSql('ALTER TABLE booking DROP FOREIGN KEY FK_E00CEDDEC1DAFE35');
        $this->addSql('ALTER TABLE seat_resource DROP FOREIGN KEY FK_A21B8412C1DAFE35');
        $this->addSql('DROP TABLE booking');
        $this->addSql('DROP TABLE building');
        $this->addSql('DROP TABLE floor');
        $this->addSql('DROP TABLE asset');
        $this->addSql('DROP TABLE seat');
        $this->addSql('DROP TABLE seat_asset');
        $this->addSql('ALTER TABLE ldap_token CHANGE expire expire DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE user ADD active TINYINT(1) NOT NULL, DROP image, CHANGE ldap_id ldap_id INT NOT NULL, CHANGE email email VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE name name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE full_name full_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
