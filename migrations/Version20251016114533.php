<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251016114533 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE place_equipement MODIFY id INT NOT NULL');
        $this->addSql('DROP INDEX `primary` ON place_equipement');
        $this->addSql('ALTER TABLE place_equipement DROP id, CHANGE place_id place_id INT NOT NULL, CHANGE equipment_id equipment_id INT NOT NULL');
        $this->addSql('ALTER TABLE place_equipement ADD PRIMARY KEY (place_id, equipment_id)');
        $this->addSql('ALTER TABLE review MODIFY id INT NOT NULL');
        $this->addSql('DROP INDEX `primary` ON review');
        $this->addSql('ALTER TABLE review DROP id, CHANGE user_id user_id INT NOT NULL, CHANGE place_id place_id INT NOT NULL');
        $this->addSql('ALTER TABLE review ADD PRIMARY KEY (user_id, place_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE place_equipement ADD id INT AUTO_INCREMENT NOT NULL, CHANGE place_id place_id INT DEFAULT NULL, CHANGE equipment_id equipment_id INT DEFAULT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE review ADD id INT AUTO_INCREMENT NOT NULL, CHANGE user_id user_id INT DEFAULT NULL, CHANGE place_id place_id INT DEFAULT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
    }
}
