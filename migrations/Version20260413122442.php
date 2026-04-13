<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260413122442 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE adresse (id BINARY(16) NOT NULL, rue VARCHAR(255) NOT NULL, ville VARCHAR(255) NOT NULL, code_postal VARCHAR(5) NOT NULL, pays VARCHAR(100) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE chauffeur (id BINARY(16) NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) DEFAULT NULL, email VARCHAR(255) NOT NULL, telephone VARCHAR(20) NOT NULL, UNIQUE INDEX UNIQ_5CA777B8E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE client (id BINARY(16) NOT NULL, nom VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, telephone VARCHAR(20) NOT NULL, UNIQUE INDEX UNIQ_C7440455E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE livraison (id BINARY(16) NOT NULL, heure_prevue DATETIME NOT NULL, statut VARCHAR(255) NOT NULL, tournee_id BINARY(16) NOT NULL, client_id BINARY(16) NOT NULL, adresse_id BINARY(16) NOT NULL, INDEX IDX_A60C9F1FF661D013 (tournee_id), INDEX IDX_A60C9F1F19EB6921 (client_id), INDEX IDX_A60C9F1F4DE7DC5C (adresse_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE livraison_marchandise (id BINARY(16) NOT NULL, livraison_id BINARY(16) NOT NULL, marchandise_id BINARY(16) NOT NULL, INDEX IDX_3EF4AFD78E54FB25 (livraison_id), INDEX IDX_3EF4AFD7F7FBEBE (marchandise_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE marchandise (id BINARY(16) NOT NULL, nom VARCHAR(255) NOT NULL, poids DOUBLE PRECISION NOT NULL, volume DOUBLE PRECISION NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tournee (id BINARY(16) NOT NULL, date DATE NOT NULL, chauffeur_id BINARY(16) NOT NULL, INDEX IDX_EBF67D7E85C0B3BE (chauffeur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE livraison ADD CONSTRAINT FK_A60C9F1FF661D013 FOREIGN KEY (tournee_id) REFERENCES tournee (id)');
        $this->addSql('ALTER TABLE livraison ADD CONSTRAINT FK_A60C9F1F19EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE livraison ADD CONSTRAINT FK_A60C9F1F4DE7DC5C FOREIGN KEY (adresse_id) REFERENCES adresse (id)');
        $this->addSql('ALTER TABLE livraison_marchandise ADD CONSTRAINT FK_3EF4AFD78E54FB25 FOREIGN KEY (livraison_id) REFERENCES livraison (id)');
        $this->addSql('ALTER TABLE livraison_marchandise ADD CONSTRAINT FK_3EF4AFD7F7FBEBE FOREIGN KEY (marchandise_id) REFERENCES marchandise (id)');
        $this->addSql('ALTER TABLE tournee ADD CONSTRAINT FK_EBF67D7E85C0B3BE FOREIGN KEY (chauffeur_id) REFERENCES chauffeur (id)');
        $this->addSql('ALTER TABLE user ADD chauffeur_id BINARY(16) DEFAULT NULL, ADD client_id BINARY(16) DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64985C0B3BE FOREIGN KEY (chauffeur_id) REFERENCES chauffeur (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64919EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D64985C0B3BE ON user (chauffeur_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D64919EB6921 ON user (client_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE livraison DROP FOREIGN KEY FK_A60C9F1FF661D013');
        $this->addSql('ALTER TABLE livraison DROP FOREIGN KEY FK_A60C9F1F19EB6921');
        $this->addSql('ALTER TABLE livraison DROP FOREIGN KEY FK_A60C9F1F4DE7DC5C');
        $this->addSql('ALTER TABLE livraison_marchandise DROP FOREIGN KEY FK_3EF4AFD78E54FB25');
        $this->addSql('ALTER TABLE livraison_marchandise DROP FOREIGN KEY FK_3EF4AFD7F7FBEBE');
        $this->addSql('ALTER TABLE tournee DROP FOREIGN KEY FK_EBF67D7E85C0B3BE');
        $this->addSql('DROP TABLE adresse');
        $this->addSql('DROP TABLE chauffeur');
        $this->addSql('DROP TABLE client');
        $this->addSql('DROP TABLE livraison');
        $this->addSql('DROP TABLE livraison_marchandise');
        $this->addSql('DROP TABLE marchandise');
        $this->addSql('DROP TABLE tournee');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64985C0B3BE');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64919EB6921');
        $this->addSql('DROP INDEX UNIQ_8D93D64985C0B3BE ON user');
        $this->addSql('DROP INDEX UNIQ_8D93D64919EB6921 ON user');
        $this->addSql('ALTER TABLE user DROP chauffeur_id, DROP client_id');
    }
}
