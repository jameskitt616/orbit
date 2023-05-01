<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Ramsey\Uuid\Uuid;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230501151230 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create User, Transcode and representation table. Add representations';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE representation (id VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, kiloBiteRate INT NOT NULL, resolutionWidth INT NOT NULL, resolutionHeight INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE transcode (id VARCHAR(255) NOT NULL, fileName VARCHAR(255) NOT NULL, filePath VARCHAR(255) NOT NULL, randSubTargetPath INT NOT NULL, transcodeFormat VARCHAR(255) NOT NULL, transcodingProgress INT NOT NULL, ownedBy_id VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_522F4C7A752AFD0D ON transcode (ownedBy_id)');
        $this->addSql('CREATE TABLE transcode_representation (transcode_id VARCHAR(255) NOT NULL, representation_id VARCHAR(255) NOT NULL, PRIMARY KEY(transcode_id, representation_id))');
        $this->addSql('CREATE INDEX IDX_BBFD8F24C83A15B4 ON transcode_representation (transcode_id)');
        $this->addSql('CREATE INDEX IDX_BBFD8F2446CE82F4 ON transcode_representation (representation_id)');
        $this->addSql('CREATE TABLE user_user (id VARCHAR(255) NOT NULL, username VARCHAR(255) NOT NULL, password VARCHAR(512) NOT NULL, isAdmin BOOLEAN NOT NULL, loginFailureCounter INT NOT NULL, roles JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE transcode ADD CONSTRAINT FK_522F4C7A752AFD0D FOREIGN KEY (ownedBy_id) REFERENCES user_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE transcode_representation ADD CONSTRAINT FK_BBFD8F24C83A15B4 FOREIGN KEY (transcode_id) REFERENCES transcode (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE transcode_representation ADD CONSTRAINT FK_BBFD8F2446CE82F4 FOREIGN KEY (representation_id) REFERENCES representation (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql("INSERT INTO representation (id, name, kiloBiteRate, resolutionWidth, resolutionHeight) VALUES ('". Uuid::uuid4()->toString(). "', '144p', 95, 256, 144);");
        $this->addSql("INSERT INTO representation (id, name, kiloBiteRate, resolutionWidth, resolutionHeight) VALUES ('". Uuid::uuid4()->toString(). "', '240p', 150, 426, 240);");
        $this->addSql("INSERT INTO representation (id, name, kiloBiteRate, resolutionWidth, resolutionHeight) VALUES ('". Uuid::uuid4()->toString(). "', '360p', 276, 640, 360);");
        $this->addSql("INSERT INTO representation (id, name, kiloBiteRate, resolutionWidth, resolutionHeight) VALUES ('". Uuid::uuid4()->toString(). "', '480p', 750, 854, 480);");
        $this->addSql("INSERT INTO representation (id, name, kiloBiteRate, resolutionWidth, resolutionHeight) VALUES ('". Uuid::uuid4()->toString(). "', '720p', 2048, 1280, 720);");
        $this->addSql("INSERT INTO representation (id, name, kiloBiteRate, resolutionWidth, resolutionHeight) VALUES ('". Uuid::uuid4()->toString(). "', '1080p', 4096, 1920, 1080);");
        $this->addSql("INSERT INTO representation (id, name, kiloBiteRate, resolutionWidth, resolutionHeight) VALUES ('". Uuid::uuid4()->toString(). "', '1440p', 6144, 2560, 1440);");
        $this->addSql("INSERT INTO representation (id, name, kiloBiteRate, resolutionWidth, resolutionHeight) VALUES ('". Uuid::uuid4()->toString(). "', '2160p', 17408, 3840, 2160);");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
