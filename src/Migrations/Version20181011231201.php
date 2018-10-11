<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181011231201 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE payment_supplier ADD supplier_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE payment_supplier ADD CONSTRAINT FK_DBEC2E162ADD6D8C FOREIGN KEY (supplier_id) REFERENCES supplier (id)');
        $this->addSql('CREATE INDEX IDX_DBEC2E162ADD6D8C ON payment_supplier (supplier_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE payment_supplier DROP FOREIGN KEY FK_DBEC2E162ADD6D8C');
        $this->addSql('DROP INDEX IDX_DBEC2E162ADD6D8C ON payment_supplier');
        $this->addSql('ALTER TABLE payment_supplier DROP supplier_id');
    }
}
