<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181009161040 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE project ADD note LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE allocate ADD note LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE payment_driver ADD note LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE payment_supplier ADD note LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE mission ADD note LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE allocate DROP note');
        $this->addSql('ALTER TABLE mission DROP note');
        $this->addSql('ALTER TABLE payment_driver DROP note');
        $this->addSql('ALTER TABLE payment_supplier DROP note');
        $this->addSql('ALTER TABLE project DROP note');
    }
}
