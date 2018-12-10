<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181011153223 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE driver_vehicle_type (driver_id INT NOT NULL, vehicle_type_id INT NOT NULL, INDEX IDX_DB20648AC3423909 (driver_id), INDEX IDX_DB20648ADA3FD1FC (vehicle_type_id), PRIMARY KEY(driver_id, vehicle_type_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vehicle_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE driver_vehicle_type ADD CONSTRAINT FK_DB20648AC3423909 FOREIGN KEY (driver_id) REFERENCES driver (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE driver_vehicle_type ADD CONSTRAINT FK_DB20648ADA3FD1FC FOREIGN KEY (vehicle_type_id) REFERENCES vehicle_type (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE vehicle ADD type_id INT NOT NULL, DROP type');
        $this->addSql('ALTER TABLE vehicle ADD CONSTRAINT FK_1B80E486C54C8C93 FOREIGN KEY (type_id) REFERENCES vehicle_type (id)');
        $this->addSql('CREATE INDEX IDX_1B80E486C54C8C93 ON vehicle (type_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE driver_vehicle_type DROP FOREIGN KEY FK_DB20648ADA3FD1FC');
        $this->addSql('ALTER TABLE vehicle DROP FOREIGN KEY FK_1B80E486C54C8C93');
        $this->addSql('DROP TABLE driver_vehicle_type');
        $this->addSql('DROP TABLE vehicle_type');
        $this->addSql('DROP INDEX IDX_1B80E486C54C8C93 ON vehicle');
        $this->addSql('ALTER TABLE vehicle ADD type VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, DROP type_id');
    }
}
