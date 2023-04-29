<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230429083022 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create User table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_user (id VARCHAR(255) NOT NULL, username VARCHAR(255) NOT NULL, password VARCHAR(512) NOT NULL, loginFailureCounter INT NOT NULL, roles JSON NOT NULL, PRIMARY KEY(id))');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
