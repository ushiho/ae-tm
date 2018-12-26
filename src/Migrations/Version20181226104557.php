<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181226104557 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE fuel_reconciliation (id INT AUTO_INCREMENT NOT NULL, department_id INT NOT NULL, user_id INT NOT NULL, project_id INT NOT NULL, vehicle_id INT NOT NULL, driver_id INT NOT NULL, gas_station_id INT DEFAULT NULL, invoice_id INT DEFAULT NULL, created_at DATETIME NOT NULL, total_amount NUMERIC(50, 2) NOT NULL, total_litres NUMERIC(50, 2) NOT NULL, kilometrage NUMERIC(50, 2) NOT NULL, note VARCHAR(255) DEFAULT NULL, INDEX IDX_F2145B1AE80F5DF (department_id), INDEX IDX_F2145B1A76ED395 (user_id), INDEX IDX_F2145B1166D1F9C (project_id), UNIQUE INDEX UNIQ_F2145B1545317D1 (vehicle_id), INDEX IDX_F2145B1C3423909 (driver_id), INDEX IDX_F2145B1916BFF50 (gas_station_id), INDEX IDX_F2145B12989F1FD (invoice_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE gas_station (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, address VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE invoice (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, total_amounts NUMERIC(50, 2) NOT NULL, total_litres NUMERIC(50, 2) NOT NULL, is_paid TINYINT(1) NOT NULL, number INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE fuel_reconciliation ADD CONSTRAINT FK_F2145B1AE80F5DF FOREIGN KEY (department_id) REFERENCES department (id)');
        $this->addSql('ALTER TABLE fuel_reconciliation ADD CONSTRAINT FK_F2145B1A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE fuel_reconciliation ADD CONSTRAINT FK_F2145B1166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
        $this->addSql('ALTER TABLE fuel_reconciliation ADD CONSTRAINT FK_F2145B1545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id)');
        $this->addSql('ALTER TABLE fuel_reconciliation ADD CONSTRAINT FK_F2145B1C3423909 FOREIGN KEY (driver_id) REFERENCES driver (id)');
        $this->addSql('ALTER TABLE fuel_reconciliation ADD CONSTRAINT FK_F2145B1916BFF50 FOREIGN KEY (gas_station_id) REFERENCES gas_station (id)');
        $this->addSql('ALTER TABLE fuel_reconciliation ADD CONSTRAINT FK_F2145B12989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE fuel_reconciliation DROP FOREIGN KEY FK_F2145B1916BFF50');
        $this->addSql('ALTER TABLE fuel_reconciliation DROP FOREIGN KEY FK_F2145B12989F1FD');
        $this->addSql('DROP TABLE fuel_reconciliation');
        $this->addSql('DROP TABLE gas_station');
        $this->addSql('DROP TABLE invoice');
    }
}
