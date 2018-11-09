<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181108193821 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE payment_supplier DROP total_price_paid, DROP remaining_price');
        $this->addSql('ALTER TABLE payment_driver DROP price_paid, DROP remaining_price');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE payment_driver ADD price_paid NUMERIC(50, 2) NOT NULL, ADD remaining_price NUMERIC(50, 2) NOT NULL');
        $this->addSql('ALTER TABLE payment_supplier ADD total_price_paid NUMERIC(50, 2) NOT NULL, ADD remaining_price NUMERIC(50, 2) NOT NULL');
    }
}
