<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250731110204 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE download (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, solution_id INT DEFAULT NULL, date DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', INDEX IDX_781A8270A76ED395 (user_id), INDEX IDX_781A82701C0BE183 (solution_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE solution (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, framework VARCHAR(255) DEFAULT NULL, domaine VARCHAR(255) DEFAULT NULL, image_path VARCHAR(255) DEFAULT NULL, zip_file_path VARCHAR(255) DEFAULT NULL, prix DOUBLE PRECISION DEFAULT NULL, produit_type VARCHAR(255) DEFAULT NULL, date_ajout DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE download ADD CONSTRAINT FK_781A8270A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE download ADD CONSTRAINT FK_781A82701C0BE183 FOREIGN KEY (solution_id) REFERENCES solution (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE download DROP FOREIGN KEY FK_781A8270A76ED395');
        $this->addSql('ALTER TABLE download DROP FOREIGN KEY FK_781A82701C0BE183');
        $this->addSql('DROP TABLE download');
        $this->addSql('DROP TABLE solution');
    }
}
