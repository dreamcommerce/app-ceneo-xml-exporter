<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151102135728 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE AttributeMapping ADD shop_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE AttributeMapping ADD CONSTRAINT FK_4AB6A7B24D16C4DD FOREIGN KEY (shop_id) REFERENCES Shop (id)');
        $this->addSql('CREATE INDEX IDX_4AB6A7B24D16C4DD ON AttributeMapping (shop_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX IDX_4AB6A7B24D16C4DD ON AttributeMapping');
        $this->addSql('ALTER TABLE AttributeMapping DROP shop_id');
    }
}
