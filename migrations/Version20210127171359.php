<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210127171359 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('create table messenger_messages(id bigint auto_increment primary key, body longtext not null,headers longtext not null,queue_name varchar(190) not null,created_at datetime not null,available_at datetime not null,delivered_at datetime null)collate = utf8mb4_unicode_ci;');
        $this->addSql('create index IDX_75EA56E016BA31DB on messenger_messages (delivered_at);');
        $this->addSql('create index IDX_75EA56E0E3BD61CE on messenger_messages (available_at);');
        $this->addSql('create index IDX_75EA56E0FB7336F0 on messenger_messages (queue_name);');
    }

    public function isTransactional(): bool
    {
        return false;
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_75EA56E0FB7336F0 ON messenger_messages;');
        $this->addSql('DROP INDEX IDX_75EA56E0E3BD61CE ON messenger_messages;');
        $this->addSql('DROP INDEX IDX_75EA56E016BA31DB ON messenger_messages;');
        $this->addSql('DROP TABLE messenger_messages;');
    }
}
