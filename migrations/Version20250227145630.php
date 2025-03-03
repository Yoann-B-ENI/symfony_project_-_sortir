<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250227145630 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE notif_message (id INT AUTO_INCREMENT NOT NULL, message LONGTEXT NOT NULL, is_flagged TINYINT(1) NOT NULL, roles VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_notif_message (user_id INT NOT NULL, notif_message_id INT NOT NULL, INDEX IDX_A48E36CBA76ED395 (user_id), INDEX IDX_A48E36CB5B2C1BA (notif_message_id), PRIMARY KEY(user_id, notif_message_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_notif_message ADD CONSTRAINT FK_A48E36CBA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_notif_message ADD CONSTRAINT FK_A48E36CB5B2C1BA FOREIGN KEY (notif_message_id) REFERENCES notif_message (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_notif_message DROP FOREIGN KEY FK_A48E36CBA76ED395');
        $this->addSql('ALTER TABLE user_notif_message DROP FOREIGN KEY FK_A48E36CB5B2C1BA');
        $this->addSql('DROP TABLE notif_message');
        $this->addSql('DROP TABLE user_notif_message');
    }
}
