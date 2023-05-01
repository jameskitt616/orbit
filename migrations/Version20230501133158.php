<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230501133158 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create User and Transcode table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE transcode (id VARCHAR(255) NOT NULL, fileName VARCHAR(255) NOT NULL, filePath VARCHAR(255) NOT NULL, randSubTargetPath INT NOT NULL, ownedBy_id VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_522F4C7A752AFD0D ON transcode (ownedBy_id)');
        $this->addSql('CREATE TABLE user_user (id VARCHAR(255) NOT NULL, username VARCHAR(255) NOT NULL, password VARCHAR(512) NOT NULL, isAdmin BOOLEAN NOT NULL, loginFailureCounter INT NOT NULL, roles JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE transcode ADD CONSTRAINT FK_522F4C7A752AFD0D FOREIGN KEY (ownedBy_id) REFERENCES user_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE transcode DROP CONSTRAINT FK_522F4C7A752AFD0D');
        $this->addSql('DROP TABLE transcode');
        $this->addSql('DROP TABLE user_user');
    }
}
