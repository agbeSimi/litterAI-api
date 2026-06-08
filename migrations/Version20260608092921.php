<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260608092921 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE classe (id INT AUTO_INCREMENT NOT NULL, professeur_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, nom_purifie VARCHAR(50) NOT NULL, effectif INT NOT NULL, modules_autoriser LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', mode_apprentissage VARCHAR(255) NOT NULL, INDEX IDX_8F87BF96BAB22EE9 (professeur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, classe_id INT DEFAULT NULL, login VARCHAR(180) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, mail_academique VARCHAR(255) DEFAULT NULL, code_verif VARCHAR(255) DEFAULT NULL, date_expiration DATETIME DEFAULT NULL, statut_verification TINYINT(1) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, INDEX IDX_8D93D6498F5EA509 (classe_id), UNIQUE INDEX UNIQ_IDENTIFIER_LOGIN (login), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE classe ADD CONSTRAINT FK_8F87BF96BAB22EE9 FOREIGN KEY (professeur_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE `user` ADD CONSTRAINT FK_8D93D6498F5EA509 FOREIGN KEY (classe_id) REFERENCES classe (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE classe DROP FOREIGN KEY FK_8F87BF96BAB22EE9');
        $this->addSql('ALTER TABLE `user` DROP FOREIGN KEY FK_8D93D6498F5EA509');
        $this->addSql('DROP TABLE classe');
        $this->addSql('DROP TABLE `user`');
    }
}
