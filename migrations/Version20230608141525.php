<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230608141525 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE messenger_messages_id_seq CASCADE');
        $this->addSql('ALTER TABLE transcode_representation DROP CONSTRAINT fk_bbfd8f24c83a15b4');
        $this->addSql('ALTER TABLE transcode_representation DROP CONSTRAINT fk_bbfd8f2446ce82f4');
        $this->addSql('DROP TABLE transcode_representation');
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE transcode ADD representation_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE transcode ADD CONSTRAINT FK_522F4C7A46CE82F4 FOREIGN KEY (representation_id) REFERENCES representation (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_522F4C7A46CE82F4 ON transcode (representation_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
