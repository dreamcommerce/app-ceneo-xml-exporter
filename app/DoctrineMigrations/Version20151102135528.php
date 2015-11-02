<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151102135528 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Installation DROP FOREIGN KEY FK_48BB1493E030ACD');
        $this->addSql('ALTER TABLE Link DROP FOREIGN KEY FK_969E36CF3E030ACD');
        $this->addSql('ALTER TABLE Permission DROP FOREIGN KEY FK_AF14917A3E030ACD');
        $this->addSql('DROP TABLE Application');
        $this->addSql('DROP TABLE Installation');
        $this->addSql('DROP TABLE Link');
        $this->addSql('DROP TABLE Permission');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE Application (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, hash VARCHAR(255) NOT NULL, billingUrl VARCHAR(255) NOT NULL, secret VARCHAR(255) NOT NULL, appstoreSecret VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Installation (id INT AUTO_INCREMENT NOT NULL, application_id INT DEFAULT NULL, shopId VARCHAR(255) NOT NULL, authCode VARCHAR(255) DEFAULT NULL, paid DOUBLE PRECISION DEFAULT NULL, subscriptionPaid DOUBLE PRECISION DEFAULT NULL, done TINYINT(1) NOT NULL, shopUrl VARCHAR(255) NOT NULL, licenseId VARCHAR(255) NOT NULL, INDEX IDX_48BB1493E030ACD (application_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Link (id INT AUTO_INCREMENT NOT NULL, application_id INT DEFAULT NULL, object VARCHAR(255) NOT NULL, action VARCHAR(255) NOT NULL, place VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, isLayer TINYINT(1) DEFAULT NULL, INDEX IDX_969E36CF3E030ACD (application_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Permission (id INT AUTO_INCREMENT NOT NULL, application_id INT DEFAULT NULL, module VARCHAR(255) NOT NULL, permission INT NOT NULL, INDEX IDX_AF14917A3E030ACD (application_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE Installation ADD CONSTRAINT FK_48BB1493E030ACD FOREIGN KEY (application_id) REFERENCES Application (id)');
        $this->addSql('ALTER TABLE Link ADD CONSTRAINT FK_969E36CF3E030ACD FOREIGN KEY (application_id) REFERENCES Application (id)');
        $this->addSql('ALTER TABLE Permission ADD CONSTRAINT FK_AF14917A3E030ACD FOREIGN KEY (application_id) REFERENCES Application (id)');
        $this->addSql('ALTER TABLE AttributeMapping DROP FOREIGN KEY FK_4AB6A7B24D16C4DD');
    }
}
