<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230509071049 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transcode ADD audioTrackNumber INT DEFAULT NULL');
        $this->addSql('ALTER TABLE transcode ADD subtitleNumber INT DEFAULT NULL');
        $this->addSql('ALTER TABLE transcode ADD audioTrackNumberTitle VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE transcode ADD subtitleNumberTitle VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
